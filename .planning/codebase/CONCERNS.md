# Codebase Concerns

**Analysis Date:** 2026-03-27

## Tech Debt

**Hardcoded Admin Email in Middleware:**
- Issue: `AdminMiddleware` hardcodes `"admin@visionaries.pro"` as a fallback admin identity check alongside the `is_admin` boolean flag. This is a bypass that will silently grant admin access even if `is_admin` is false, and cannot be changed without a code deploy.
- Files: `app/Http/Middleware/AdminMiddleware.php:13`
- Impact: Any attacker who registers with that email gains admin API access. The `is_admin` flag is meaningless for that email.
- Fix approach: Remove the hardcoded email fallback. Use only `$user->is_admin`. Ensure the seeder sets `is_admin = true` for the admin user.

**Direct `env()` Calls Outside Config Files:**
- Issue: `PushNotificationService`, `OneSignalService`, and `PushController` call `env()` directly instead of using `config()`. This breaks when Laravel's config cache is active (production), since `env()` returns `null` when the config is cached.
- Files: `app/Services/PushNotificationService.php:18-20`, `app/Services/OneSignalService.php:15-16`, `app/Http/Controllers/Api/PushController.php:59`
- Impact: Push notifications silently fail in any environment that runs `php artisan config:cache` (all production setups).
- Fix approach: Add `vapid` and `onesignal` keys to `config/services.php` and replace all `env()` calls with `config('services.vapid.public_key')` etc.

**Services Not Dependency-Injected:**
- Issue: `PushNotificationService` is instantiated with `new PushNotificationService()` in five separate places rather than being injected via Laravel's service container. This makes unit testing impossible and couples callers to the concrete implementation.
- Files: `app/Http/Controllers/Api/AdminPushController.php:21`, `app/Http/Controllers/Api/PushController.php:46`, `app/Console/Commands/PushDailyMorning.php:15`, `app/Console/Commands/PushEveningCheckin.php`, `app/Console/Commands/PushWeeklyReview.php`
- Impact: Cannot mock the service in tests; VAPID config loaded at instantiation prevents testing without real keys.
- Fix approach: Bind `PushNotificationService` and `OneSignalService` in `AppServiceProvider`, inject via constructor.

**Duplicate Route Aliases:**
- Issue: Multiple route duplicates exist for the same controllers with no obvious reason: `/traits` and `/identity/library` both hit `TraitController::library`; `/feeds` and `/community/feeds` both hit `CommunityController::feeds`; `/calendar/events` and `/islamic-events` both hit `CalendarController::events`; `/journal/entries` and `/journal` both hit `JournalController::index`. Comments say "Alternative endpoint".
- Files: `routes/api.php:51-52`, `routes/api.php:77-78`, `routes/api.php:85-86`, `routes/api.php:69-70`
- Impact: Doubles the surface area for documentation, testing, and maintenance. Inconsistent response shapes are likely if routes diverge later.
- Fix approach: Choose one canonical route per resource. Deprecate and remove alternatives.

**Suggestions Are Hardcoded Static Data:**
- Issue: `SuggestionController` contains a static PHP array of 12 hardcoded suggestions with no database backing. The "AI reasoning" fields are fabricated strings, not actual AI output. The feature name ("AI Suggestions") implies a dynamic, personalized system.
- Files: `app/Http/Controllers/Api/SuggestionController.php:11-24`
- Impact: Suggestions cannot be updated without a code deploy. There is no actual personalization to user vision or traits despite the `aiReasoning` field implying it.
- Fix approach: Move suggestions to a database table. Either implement real AI-based personalization or remove the `aiReasoning` field and rename the feature honestly.

**`UserFactory` References Non-Existent `name` Column:**
- Issue: `UserFactory::definition()` sets a `name` field (`fake()->name()`), but the `users` table uses `display_name`. This factory will throw a database error if used.
- Files: `database/factories/UserFactory.php:24`
- Impact: All factory-based tests will fail at the database layer. Since there are almost no tests, this is invisible today but blocks test development.
- Fix approach: Replace `'name'` with `'display_name'` in the factory definition.

**`SettingsController::export` References Non-Existent `logs` Relationship:**
- Issue: The data export endpoint calls `$user->actions()->with('logs')->get()`. The `Action` model has no `logs` relationship; `checkIns()` is the correct relationship name.
- Files: `app/Http/Controllers/Api/SettingsController.php:23`, `app/Models/Action.php`
- Impact: The `/settings/export` endpoint either silently returns empty arrays for action logs or throws an error depending on Laravel version behavior with undefined eager-load relationships.
- Fix approach: Replace `->with('logs')` with `->with('checkIns')`.

**`SettingsController::updateLocale` Sets `locale` But Column Is `language`:**
- Issue: `SettingsController::updateLocale` validates and saves `$data['locale']` to the user. The `users` table column is `language`, not `locale`. The `$fillable` array also lists `language`.
- Files: `app/Http/Controllers/Api/SettingsController.php:11-13`, `app/Models/User.php:16`
- Impact: Locale updates via this endpoint either silently fail or throw a database column-not-found error.
- Fix approach: Change validation key from `'locale'` to `'language'` in `SettingsController::updateLocale`.

## Known Bugs

**`FriendController` and `ReelController` Reference Missing Models:**
- Symptoms: Any request to friend or reel interaction endpoints throws a fatal PHP class-not-found error.
- Files: `app/Http/Controllers/Api/FriendController.php:6` (`use App\Models\Friend`), `app/Http/Controllers/Api/ReelController.php:7` (`use App\Models\ReelInteraction`)
- Trigger: No `Friend` model file and no `ReelInteraction` model file exist anywhere in `app/Models/`. No migration for a `friends` table or `reel_interactions` table exists either.
- Workaround: FriendController and ReelController are not registered in `routes/api.php` so no active routes hit them, but the code is broken and channels.php references `\App\Models\Friend` which would fail at broadcast auth time.

**`FriendController::search` Queries Non-Existent `name` Column:**
- Symptoms: User search returns a SQL error or empty results.
- Files: `app/Http/Controllers/Api/FriendController.php:66`
- Trigger: The query uses `where('name', 'ilike', ...)` but the `users` table has `display_name`, not `name`. `ILIKE` is also PostgreSQL syntax; the project uses SQLite, which does not support `ILIKE`.
- Workaround: None — the endpoint would fail with both a column error and a SQL syntax error on SQLite.

**`CommunityController::createPost` Calls Undefined Relationship:**
- Symptoms: Creating a community post throws a `BadMethodCallException`.
- Files: `app/Http/Controllers/Api/CommunityController.php:24`, `app/Models/User.php`
- Trigger: `$request->user()->communityPosts()->create($data)` — the `User` model has no `communityPosts()` relationship defined.
- Workaround: None for the current route. The route is registered and reachable.

**`CalendarController::upcoming()` Has No Route:**
- Symptoms: The `upcoming()` method is dead code — no route points to it.
- Files: `app/Http/Controllers/Api/CalendarController.php:16-22`, `routes/api.php`
- Trigger: Route file only registers `events()`. `upcoming()` is unreachable.

**`QuizController`, `ReelController` Have No Routes:**
- Symptoms: Quiz and reel features are inaccessible from the API.
- Files: `routes/api.php`, `app/Http/Controllers/Api/QuizController.php`, `app/Http/Controllers/Api/ReelController.php`
- Trigger: Neither controller is imported or registered in `routes/api.php`.

## Security Considerations

**Hardcoded Fallback Admin Email:**
- Risk: Any user who registers with `admin@visionaries.pro` bypasses the `is_admin` check and gains full admin API access.
- Files: `app/Http/Middleware/AdminMiddleware.php:13`
- Current mitigation: None — the hardcoded email check is additive to `is_admin`.
- Recommendations: Remove the email fallback. Admin status should be controlled exclusively via the database `is_admin` flag.

**Google ID Token Verification Not Using a Library:**
- Risk: `AuthController::googleToken` manually calls Google's `tokeninfo` endpoint and checks `aud`. This approach is deprecated by Google and subject to network failures. It does not check token expiry (`exp` field) nor the `email_verified` claim.
- Files: `app/Http/Controllers/Api/AuthController.php:118-130`
- Current mitigation: The `aud` (audience) check is present.
- Recommendations: Use `google/apiclient` or Firebase Admin SDK to verify the token locally without a network call. Also verify `exp` and `email_verified` fields from the payload.

**Weak Password Policy:**
- Risk: The registration endpoint enforces only `min:8` with no complexity requirements. No protection against common passwords or credential stuffing.
- Files: `app/Http/Controllers/Api/AuthController.php:19`
- Current mitigation: Sanctum tokens expire only on explicit logout.
- Recommendations: Add `Rules\Password::defaults()` with `Laravel\Rules\Password::min(8)->mixedCase()->numbers()` or similar.

**VAPID Public Key Exposed Without Authentication:**
- Risk: `/push/vapid-key` is a public, unauthenticated endpoint that calls `env('VAPID_PUBLIC_KEY')` directly. While the public key is inherently public, the direct `env()` call breaks under config caching.
- Files: `app/Http/Controllers/Api/PushController.php:57-60`, `routes/api.php:22`
- Current mitigation: The public VAPID key is not secret by design.
- Recommendations: Move to `config('services.vapid.public_key')`.

**Denormalized Counts Are Not Transactionally Safe:**
- Risk: `likes_count` and `comments_count` on `community_posts` are incremented/decremented with `increment()`/`decrement()` outside of any database transaction. A concurrent like/unlike race condition can corrupt the counter.
- Files: `app/Http/Controllers/Api/CommunityController.php:35-36`, `app/Http/Controllers/Api/CommunityController.php:53`
- Current mitigation: SQLite's write-ahead lock provides some sequential protection in development; this is a real risk with PostgreSQL/MySQL.
- Recommendations: Wrap check+create+increment in a DB transaction, or compute counts dynamically via `withCount`.

## Performance Bottlenecks

**`PushNotificationService::sendToAll` Loads All Subscriptions Into Memory:**
- Problem: Broadcast to all users fetches every `PushSubscription` row at once with `PushSubscription::with('user')->get()`. With many users, this will exhaust PHP memory.
- Files: `app/Services/PushNotificationService.php:65`
- Cause: No chunking or lazy loading.
- Improvement path: Use `PushSubscription::with('user')->chunk(100, function($subscriptions) { ... })` to process in batches.

**`DashboardController::today` Executes Multiple Separate Queries:**
- Problem: The dashboard endpoint runs at least four separate queries (vision, actions, check-ins, daily check-in) with no caching. Called on every mobile app open.
- Files: `app/Http/Controllers/Api/DashboardController.php`
- Cause: No eager loading across user relations; each is fetched independently.
- Improvement path: Wrap in a short-lived cache keyed by `user:{id}:dashboard:{date}`.

**`CalendarController::events` Returns All Islamic Events Without Pagination:**
- Problem: `IslamicEvent::orderBy('event_date')->get()` returns the entire table as one JSON blob.
- Files: `app/Http/Controllers/Api/CalendarController.php:11-13`
- Cause: No pagination or date-range filter.
- Improvement path: Add `->paginate()` or filter to current year/upcoming events only.

**Community Feed Loads Full Comments for Every Post:**
- Problem: `CommunityPost::with(['user', 'comments.user'])->latest()->paginate(20)` eagerly loads all comments and their users for 20 posts. A post with 200 comments loads all 200 in the feed.
- Files: `app/Http/Controllers/Api/CommunityController.php:15-18`
- Cause: Unbounded nested eager load.
- Improvement path: Remove `comments.user` from the feed query. Comments should be loaded only when a post is opened via a dedicated endpoint (`GET /community/posts/{id}/comments`).

## Fragile Areas

**`likes_count` / `comments_count` Denormalization:**
- Files: `app/Models/CommunityPost.php`, `app/Http/Controllers/Api/CommunityController.php`
- Why fragile: Counters are maintained manually with `increment()`/`decrement()`. If a like is deleted without going through the controller (e.g., direct DB delete, cascade, admin panel), the count becomes stale with no recovery path.
- Safe modification: Avoid direct database deletions of likes/comments. Any admin moderation must go through the controller methods or explicitly recalculate counts.
- Test coverage: No tests exist for these counter operations.

**Vision `VisionVersion` Snapshot Logic:**
- Files: `app/Http/Controllers/Api/VisionController.php:32-44`
- Why fragile: Version snapshotting happens inside the controller, not in an Eloquent observer or model event. If the vision is updated from any other code path (e.g., admin panel, seeder), no snapshot is created. The `max('version_number')` query is not wrapped in a transaction, allowing a race condition to create duplicate version numbers.
- Safe modification: Move snapshot logic to a `VisionObserver` and use a DB transaction.
- Test coverage: None.

**`TraitController::createCustom` Creates Global Traits:**
- Files: `app/Http/Controllers/Api/TraitController.php:85-96`
- Why fragile: Custom traits created by any authenticated user are stored in the shared `traits` table with `is_default = false`. There is no `user_id` on the `traits` table to scope them, meaning all custom traits are globally visible to every user who queries the library. Any user can pollute the trait library.
- Safe modification: Add a `user_id` foreign key to `traits` for custom entries, or store custom traits directly on `user_traits` without a `traits` table entry.
- Test coverage: None.

## Scaling Limits

**Push Notifications (Web Push):**
- Current capacity: Processes all subscriptions in a single synchronous PHP request or artisan command execution.
- Limit: At roughly 500+ subscriptions, the command will time out or run out of memory. The `sendToAll` result array also grows unboundedly.
- Scaling path: Move to a queued job per user batch. Use `dispatch()` with chunked subscription queries.

**SQLite Default Database:**
- Current capacity: Suitable for development and low-traffic single-server production.
- Limit: No concurrent writes. Community feeds, check-ins, and action check-ins all write frequently. High concurrency will cause `SQLITE_BUSY` errors.
- Scaling path: Migrate to PostgreSQL or MySQL for any production deployment with more than a handful of concurrent users.

## Dependencies at Risk

**`minishlink/web-push` (^10.0):**
- Risk: Web Push (VAPID) support in browsers is complete, but this library requires OpenSSL and VAPID keys to be configured. If VAPID keys are missing or malformed, the service throws at construction time and brings down any request that instantiates it.
- Impact: The `PushNotificationService` constructor will throw if `VAPID_PUBLIC_KEY` or `VAPID_PRIVATE_KEY` are absent, causing 500 errors on push test and broadcast endpoints.
- Migration plan: Add an `isConfigured()` guard check (similar to `OneSignalService`) to the service constructor or wrap construction in a try/catch.

## Missing Critical Features

**Password Reset:**
- Problem: There is a `password_reset_tokens` table (in the users migration) and Laravel's built-in password reset infra, but no routes, controllers, or notifications are wired up for password reset.
- Blocks: Users who registered with email+password and forget their password have no recovery path. There is no `/auth/forgot-password` or `/auth/reset-password` route.

**Email Verification Enforcement:**
- Problem: The `email_verified` boolean is set on registration (to `false` for email auth, `true` for Google) but is never checked on any endpoint. The `email_verified_at` timestamp column also exists but is unused.
- Blocks: Unverified email users have full API access with no verification gate.

**Friend System Infrastructure:**
- Problem: The `FriendController`, `FriendRequestReceived` event, `FriendRequestNotification`, and a `friends.{id}` broadcast channel are all implemented, but the `Friend` model, `friends` database migration, and all friend routes are absent.
- Blocks: The entire social/friendship feature is non-functional. Broadcast auth for the `friends.{id}` channel will throw a class-not-found error.

**Reel Interaction Infrastructure:**
- Problem: `ReelController::like` and `ReelController::save` reference `App\Models\ReelInteraction` which does not exist. No migration for a `reel_interactions` table exists.
- Blocks: Any reel like/save action will throw a fatal error. No routes exist anyway, so this is unreachable but represents incomplete feature work.

## Test Coverage Gaps

**Virtually No Tests Exist:**
- What's not tested: Every API endpoint, all authentication flows (register, login, Google SSO), all business rules (trait limits, action overload warning, vision versioning), push notification delivery, admin middleware.
- Files: `tests/Feature/ExampleTest.php` (1 smoke test), `tests/Unit/ExampleTest.php` (1 unit test stub)
- Risk: Any regression is invisible until a user reports it in production. The `UserFactory` is broken (`name` vs `display_name`), meaning even adding tests would fail immediately without a fix.
- Priority: High

**No Test for Admin Authorization:**
- What's not tested: The `AdminMiddleware` hardcoded email bypass is not tested, meaning the security vulnerability could be introduced or changed without detection.
- Files: `app/Http/Middleware/AdminMiddleware.php`
- Risk: Admin endpoints could become publicly accessible without any test catching it.
- Priority: High

**No Test for Counter Integrity:**
- What's not tested: `likes_count` and `comments_count` correctness on concurrent operations.
- Files: `app/Http/Controllers/Api/CommunityController.php`
- Risk: Counter drift in production goes undetected.
- Priority: Medium

---

*Concerns audit: 2026-03-27*
