# Pitfalls Research

**Domain:** Laravel API backend parity and hardening (routing unrouted controllers, Claude API integration, API resource refactoring, test bootstrapping, data migration)
**Researched:** 2026-03-27
**Confidence:** HIGH (grounded in codebase analysis + verified external sources)

## Critical Pitfalls

### Pitfall 1: Routing Controllers That Reference Non-Existent Models and Migrations

**What goes wrong:**
`FriendController` imports `App\Models\Friend` and `ReelController` imports `App\Models\ReelInteraction` -- neither model nor migration exists. Registering these routes without first creating the models and migrations causes immediate fatal errors (class not found) on every request to those endpoints. Additionally, `channels.php` already references `App\Models\Friend`, meaning broadcast channel authorization is silently broken right now.

**Why it happens:**
Controllers were written speculatively before models/migrations. Developers assume "just add the route" is sufficient to activate a feature, without tracing the full dependency chain from route to controller to model to migration to database table.

**How to avoid:**
Before adding any route, trace the full call chain: controller imports, model references, relationship calls, migration existence. Create models and migrations BEFORE registering routes. For this project specifically:
1. Create `Friend` model + `friends` migration (columns: id, user_id, friend_id, status, timestamps)
2. Create `ReelInteraction` model + `reel_interactions` migration (columns: id, user_id, reel_id, type, timestamps)
3. Fix `FriendController::search` -- uses `name` column (should be `display_name`) and `ILIKE` (PostgreSQL syntax, not SQLite-compatible -- use `LIKE` with case-insensitive collation or `whereRaw('LOWER(display_name) LIKE ?', ...)`)
4. Fix `FriendController::sendRequest` -- references `FriendRequestReceived` and `FriendRequestNotification` without imports
5. THEN register routes

**Warning signs:**
- `php artisan route:list` throws class-not-found errors
- Any `use App\Models\X` at the top of a controller where X does not exist in `app/Models/`
- Running `php artisan test` before routing shows import failures

**Phase to address:**
Phase 1 (Route Missing Controllers) -- this must be the very first thing done, before any new endpoints or tests.

---

### Pitfall 2: Breaking Frontend Response Shapes When Refactoring API Resources

**What goes wrong:**
All 13 API Resource classes currently use `parent::toArray($request)` (pass-through), which exposes every database column including `password`, timestamps, and internal fields. When you refactor these to explicit field lists, you will inevitably omit a field the frontend depends on, rename a key the frontend expects (e.g., `created_at` vs `createdAt`), or change the nesting structure. The React frontend has 70+ API calls -- any response shape change breaks the app silently (no compile error, just missing data or undefined field reads).

**Why it happens:**
Pass-through resources create an implicit contract between the database schema and the API response. The frontend has been coded against this implicit contract for months. Refactoring to explicit fields means you are defining the contract for the first time, and any deviation from what the frontend already consumes is a breaking change.

**How to avoid:**
1. Before touching any resource, audit the frontend to document the exact fields it reads from each endpoint response. Create a response contract document.
2. Refactor one resource at a time, not all at once.
3. Write a feature test for each endpoint BEFORE refactoring its resource -- the test encodes the expected response shape, so any field removal fails the test.
4. Use `$this->resource->relationLoaded('x')` checks to conditionally include relations, not blindly eager-load.
5. Never rename JSON keys -- if the frontend reads `created_at`, keep it `created_at` even if you prefer camelCase.
6. Add fields (safe) before removing fields (breaking). If you must remove, check frontend usage first.

**Warning signs:**
- Frontend shows blank or `undefined` where data should appear
- JavaScript console errors about accessing properties of undefined
- API responses suddenly smaller than before
- Tests that assert response structure start failing

**Phase to address:**
Phase 3 or later (API Resource Hardening) -- AFTER routes are wired up and tests are written for existing response shapes. Never refactor resources before you have tests that lock down the current contract.

---

### Pitfall 3: Broken UserFactory Blocks All Test Development

**What goes wrong:**
`UserFactory::definition()` sets `'name' => fake()->name()` but the `users` table column is `display_name`. Every test that calls `User::factory()->create()` immediately throws a SQL column error. Since the factory is the foundation of all feature tests, this single bug blocks the entire testing effort.

**Why it happens:**
The factory was scaffolded by Laravel's default `make:model` command, which generates a `name` field. Nobody updated it because there were no tests to exercise it.

**How to avoid:**
Fix the factory FIRST, before writing any tests. The corrected factory should match the actual users table schema:
- Replace `'name'` with `'display_name'`
- Add any other required columns that don't have database defaults (check migration for non-nullable columns without defaults)
- Verify by running `User::factory()->create()` in `php artisan tinker`

**Warning signs:**
- First test you write fails with a database error, not a business logic error
- `SQLSTATE[HY000]: General error: 1 table users has no column named name`

**Phase to address:**
Phase 1 (Foundation) -- must be fixed before any test can be written. This is a 2-minute fix that unblocks all subsequent work.

---

### Pitfall 4: Claude API Integration Without Proper Timeout, Retry, and Cost Controls

**What goes wrong:**
The Claude API has three distinct failure modes that differ from typical REST APIs: (1) 429 rate limits with three independent limits (requests/min, tokens/min, daily token quota), (2) 529 overloaded errors that are transient and should be retried, (3) long response times (10-60 seconds for complex prompts). A naive `Http::post()` call with Laravel's default 30-second timeout will randomly fail, and without retry logic, users see intermittent 500 errors. Without token estimation, a single malicious or large request can exhaust your daily API budget.

**Why it happens:**
Developers treat the Claude API like a fast REST service (sub-second responses, binary success/failure). AI APIs are fundamentally different: responses are slow, failures are often transient, and costs are per-token not per-request.

**How to avoid:**
1. Create a dedicated `ClaudeService` class (not inline HTTP calls in controllers). Inject via service container for testability.
2. Set explicit timeout: `Http::timeout(60)->retry(3, 200, fn($e) => $e->response?->status() === 429 || $e->response?->status() === 529)`
3. Implement input token estimation (rough: 1 token per 4 characters). Reject requests exceeding a budget threshold (e.g., 8,000 input tokens for suggestions).
4. Queue long-running AI calls with `dispatch()` and return a 202 Accepted with a polling endpoint, OR use Laravel's async HTTP if the response can be waited for.
5. Cache AI responses keyed by user context hash -- if a user's vision/traits haven't changed, serve the cached suggestion.
6. Store API key in `config/services.php` (NOT `env()` directly) to avoid the same bug already present in `PushNotificationService`.
7. Log the Anthropic `request-id` header on every call for debugging.
8. Handle the specific error types: 401 (bad key -- alert admin), 429 (rate limit -- backoff), 529 (overloaded -- retry), 400 (bad request -- log and fix).

**Warning signs:**
- Suggestion generation endpoint has >10% error rate in logs
- Monthly Anthropic bill spikes unexpectedly
- Users report "suggestions not loading" intermittently
- `env('ANTHROPIC_API_KEY')` returns null in production (same bug pattern as VAPID keys)

**Phase to address:**
Phase 2 (New Endpoints) -- when implementing `POST /suggestions/generate`. The service class and error handling must be designed upfront, not bolted on.

---

### Pitfall 5: Adding Tests to a Codebase With Hidden Broken Code

**What goes wrong:**
When you start writing feature tests for an untested codebase, the first 10+ tests will fail for reasons unrelated to what you're testing: broken factory (`name` vs `display_name`), missing model relationships (`communityPosts()` on User), wrong column names (`locale` vs `language` in SettingsController), non-existent eager-load relationships (`logs` vs `checkIns` in export), hardcoded admin email bypass. Each failing test becomes a debugging session into pre-existing bugs rather than validating new behavior. Teams get demoralized and abandon testing.

**Why it happens:**
Untested code accumulates latent bugs that are invisible until something exercises the code paths. The act of writing tests is also the act of discovering every broken code path. If you don't budget for this discovery work, test writing feels impossibly slow.

**How to avoid:**
1. Budget the first phase of testing as "fix broken foundations" not "write tests." Expect 50% of initial test time to be spent fixing pre-existing bugs.
2. Fix these known broken items BEFORE writing any feature tests:
   - `UserFactory` (`name` -> `display_name`)
   - `User` model: add missing `communityPosts()` relationship
   - `SettingsController::updateLocale`: `locale` -> `language`
   - `SettingsController::export`: `logs` -> `checkIns`
   - `FriendController::search`: `name` -> `display_name`, `ILIKE` -> `LIKE`
3. Start with a smoke test (`GET /api/dashboard/today` returns 200) to verify the test harness itself works.
4. Test new code (newly routed endpoints) before testing old code. New code has fewer hidden surprises.
5. Use `RefreshDatabase` trait, not `DatabaseTransactions`, to ensure clean state between tests.

**Warning signs:**
- First feature test fails on database setup, not on assertions
- Multiple tests fail with the same root cause (e.g., factory bug)
- Test suite takes over 30 minutes because of cascading failures

**Phase to address:**
Phase 1 (Foundation fixes) for the factory and known bugs. Phase 3+ for actual test writing.

---

### Pitfall 6: Moving Hardcoded Suggestions to Database Without Preserving Frontend Contract

**What goes wrong:**
The `SuggestionController` returns a specific JSON shape with fields like `id` (integer 1-12), `title`, `description`, `domain`, `category`, `impact`, `effort`, `visionConnection`, `aiReasoning`. The `add` method references suggestions by these integer IDs. When you move to a database table with UUID primary keys (per project convention), every suggestion ID changes from integer to UUID. The frontend's "add suggestion" calls will break because they're sending integer IDs. The response field names may also change if you use an API Resource that transforms keys.

**Why it happens:**
The hardcoded array used integer sequential IDs. The project uses UUID primary keys everywhere. This mismatch is easy to overlook because the migration seems straightforward.

**How to avoid:**
1. Create the `suggestions` migration with UUID primary keys (consistent with project conventions).
2. Create a `Suggestion` model with `HasUuid` trait.
3. Create a seeder that populates the same 12 suggestions.
4. Update the `SuggestionController` to query the database instead of the static array.
5. The API response must preserve the exact same JSON field names: `title`, `description`, `domain`, `category`, `impact`, `effort`, `visionConnection`, `aiReasoning`. Use an API Resource to ensure this.
6. The `id` field will change from integer to UUID -- coordinate with frontend. If frontend stores suggestion IDs locally, those references break.
7. Test the `add` endpoint accepts UUID IDs.
8. Keep the static array as a fallback during migration, or run the seeder as part of deployment.

**Warning signs:**
- Frontend "add suggestion" button stops working after backend deploy
- Suggestion IDs in API responses change format (integer -> UUID string)
- Seeder fails silently and suggestions endpoint returns empty array

**Phase to address:**
Phase 2 (New Endpoints / Data Migration) -- should be done alongside the AI suggestion generation work since both touch the suggestions domain.

---

### Pitfall 7: env() Calls Outside Config Files Break Production

**What goes wrong:**
This codebase already has `env()` called directly in `PushNotificationService`, `OneSignalService`, and `PushController`. Any new service (like `ClaudeService`) that follows the same pattern will silently return `null` for all environment variables when `php artisan config:cache` is active -- which is standard in every Laravel production deployment. The Claude API integration will appear to work in development and completely fail in production.

**Why it happens:**
In development, `env()` reads from `.env` directly. When config is cached, Laravel skips `.env` parsing entirely -- `env()` returns `null`. This is documented behavior but catches developers who only test locally.

**How to avoid:**
1. Add ALL new service credentials to `config/services.php`:
   ```php
   'anthropic' => [
       'api_key' => env('ANTHROPIC_API_KEY'),
       'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
       'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 1024),
   ],
   ```
2. In service classes, ONLY use `config('services.anthropic.api_key')`.
3. While adding the Claude config, also fix the existing `env()` calls in push notification services.
4. Run `php artisan config:cache && php artisan test` locally to catch any remaining `env()` calls.

**Warning signs:**
- Any `env()` call in `app/` directory (outside `config/`)
- Feature works locally but fails in staging/production
- Service constructors that read environment variables directly

**Phase to address:**
Phase 1 (Foundation) -- fix existing `env()` calls. Phase 2 -- ensure Claude integration uses `config()` from the start.

---

## Technical Debt Patterns

| Shortcut | Immediate Benefit | Long-term Cost | When Acceptable |
|----------|-------------------|----------------|-----------------|
| Pass-through API Resources (`parent::toArray()`) | Fast development, no field mapping | Exposes internal columns (password hashes, timestamps), breaks when schema changes, no contract for frontend | Never in production -- but fix AFTER tests lock down current shapes |
| Duplicate route aliases (`/feeds` and `/community/feeds`) | Frontend compatibility during iteration | Double testing surface, divergent behavior risk, confusing docs | Only during migration period -- deprecate within one release |
| Manual `increment()`/`decrement()` for counters | Simple, no extra queries | Race conditions corrupt counts, no self-healing, cascade deletes bypass counters | MVP only -- replace with `withCount` or DB triggers before scaling |
| Inline validation in controllers (no Form Requests) | Faster to write, less files | Validation logic not reusable, harder to test in isolation, clutters controller methods | Acceptable for simple endpoints -- use Form Requests for complex validation (quiz submission, friend requests) |
| `new Service()` instead of DI | No container binding needed | Cannot mock in tests, tight coupling, breaks if constructor args change | Never -- always use DI in Laravel |

## Integration Gotchas

| Integration | Common Mistake | Correct Approach |
|-------------|----------------|------------------|
| Anthropic Claude API | Using `env('ANTHROPIC_API_KEY')` directly in service class | Add to `config/services.php`, use `config('services.anthropic.api_key')` |
| Anthropic Claude API | Default 30-second HTTP timeout for AI generation | Set `Http::timeout(60)` minimum; consider queued jobs for complex prompts |
| Anthropic Claude API | No retry logic; single 429 kills the request | Use `Http::retry(3, 500, fn($e) => in_array($e->response?->status(), [429, 529]))` |
| Anthropic Claude API | Unbounded input tokens (user sends entire journal as context) | Estimate tokens before sending (4 chars per token), enforce max input size |
| Anthropic Claude API | Treating AI errors as server errors (500) | Return 503 (Service Unavailable) with retry-after header for transient AI failures; return 422 for input too large |
| Google OAuth (Socialite) | Manual `tokeninfo` endpoint call without checking `exp` or `email_verified` | Use `google/apiclient` library for local token verification with full claim validation |
| Web Push (minishlink) | Service throws in constructor if VAPID keys missing | Add `isConfigured()` guard check; return graceful error if push not configured |

## Performance Traps

| Trap | Symptoms | Prevention | When It Breaks |
|------|----------|------------|----------------|
| Claude API called synchronously in request cycle | Suggestion endpoint takes 5-30 seconds to respond; frontend shows loading spinner; PHP-FPM workers blocked | Queue AI calls as jobs; return 202 with polling endpoint; or cache results aggressively | Immediately at launch -- even one user notices 10+ second API responses |
| `sendToAll` push loads all subscriptions into memory | Memory exhaustion on broadcast; command times out | Use `chunk(100)` for batch processing | ~500 push subscriptions |
| Community feed loads all comments via eager load | Feed endpoint slow (>2 seconds); large JSON payloads | Load only `withCount('comments')` on feed; load full comments on post detail | ~50 comments per post |
| Dashboard endpoint runs 4+ separate queries uncached | Repeated slow loads on app open; high DB load | Cache per user per day: `Cache::remember("dashboard:{$userId}:{$date}", 300, ...)` | ~100 concurrent users |
| No pagination on Islamic events | Single endpoint returns entire events table | Add `->paginate(50)` or date-range filter | ~200 events |

## Security Mistakes

| Mistake | Risk | Prevention |
|---------|------|------------|
| Hardcoded admin email in `AdminMiddleware` | Anyone registering as `admin@visionaries.pro` gets full admin access | Remove email fallback; rely solely on `is_admin` database flag |
| Pass-through API Resources expose password hash | `parent::toArray()` includes `password` column in JSON responses | Define explicit field lists in every resource; never expose `password`, `remember_token` |
| No authorization on friend profile endpoint | Any authenticated user can view any other user's full profile via `GET /friends/{id}/profile` | Add policy check: only friends can view each other's profiles |
| No rate limit on Claude API endpoint | Attacker can exhaust Anthropic API budget with repeated requests | Add specific rate limit: `RateLimiter::for('ai', fn() => Limit::perMinute(5)->by($request->user()->id))` |
| Custom traits pollute global library | Any user's custom trait is visible to all users | Add `user_id` scope to `traits` table or store custom traits only in `user_traits` |
| Data export exposes raw user array | `$user->toArray()` in export includes all columns | Use `UserResource` for export response |

## "Looks Done But Isn't" Checklist

- [ ] **Friends feature:** Controller exists but model, migration, routes, and event imports are all missing -- verify ALL exist before marking "done"
- [ ] **Reels feature:** Controller exists but `ReelInteraction` model and migration are missing -- verify model + migration + routes
- [ ] **Suggestions migration:** Static array moved to DB but frontend still sends integer IDs -- verify frontend compatibility with UUID IDs
- [ ] **API Resources refactored:** Fields explicitly listed but `password` and `remember_token` still leak -- verify sensitive fields excluded in EVERY resource
- [ ] **Settings locale endpoint:** Route exists but writes `locale` to a column named `language` -- verify the column name matches
- [ ] **Settings export endpoint:** Route exists but eager-loads `logs` relationship that doesn't exist on Action model -- verify it uses `checkIns`
- [ ] **Test suite green:** Tests pass but `UserFactory` uses wrong column name -- verify factory creates users successfully before trusting test results
- [ ] **Claude integration works:** API calls succeed locally but `env()` returns null with config cache -- verify with `php artisan config:cache && php artisan test`
- [ ] **Push notifications in production:** Works locally but `env()` calls in services return null when cached -- verify all services use `config()` helper
- [ ] **Broadcast channels:** `channels.php` references `Friend` model -- verify model exists before enabling broadcast auth

## Recovery Strategies

| Pitfall | Recovery Cost | Recovery Steps |
|---------|---------------|----------------|
| Frontend breaks from resource refactoring | MEDIUM | Revert the resource change; write test encoding old shape; refactor again with test as guard |
| Claude API key leaked or budget exhausted | LOW | Rotate key in Anthropic console; add budget alerts; add rate limiting |
| Friend routes registered without model | LOW | Revert route registration; create model and migration first; re-register |
| Counter drift from race conditions | MEDIUM | Run one-time artisan command to recalculate all `likes_count`/`comments_count` from actual records |
| Suggestion IDs change format (int to UUID) | MEDIUM | Coordinate frontend release; or add temporary integer ID column for backwards compatibility |
| Tests discover cascade of pre-existing bugs | LOW | Treat as expected; fix bugs in priority order; don't block test writing on every bug |

## Pitfall-to-Phase Mapping

| Pitfall | Prevention Phase | Verification |
|---------|------------------|--------------|
| Broken UserFactory | Phase 1 (Foundation) | `User::factory()->create()` succeeds in tinker |
| Missing Friend/ReelInteraction models | Phase 1 (Route Missing Controllers) | `php artisan route:list` shows no errors |
| FriendController SQL bugs (name, ILIKE) | Phase 1 (Route Missing Controllers) | Feature test for friend search passes on SQLite |
| env() calls in services | Phase 1 (Foundation) | `grep -r "env(" app/` returns zero results |
| SettingsController column mismatches | Phase 1 (Foundation) | Feature tests for locale update and export pass |
| User::communityPosts() missing | Phase 1 (Foundation) | Community post creation test passes |
| Claude API timeout/retry/cost | Phase 2 (AI Integration) | ClaudeService has timeout, retry, and token estimation; integration test mocks API |
| Suggestion data migration (int to UUID) | Phase 2 (Data Migration) | Suggestion index and add endpoints return/accept UUIDs |
| API Resource refactoring breaks frontend | Phase 3 (Hardening) | Tests written BEFORE refactoring encode exact response shapes; all pass after |
| Pass-through resources expose sensitive data | Phase 3 (Hardening) | No resource response includes `password` or `remember_token` |
| Duplicate route aliases | Phase 3 (Hardening) | `php artisan route:list` shows one canonical route per resource |
| Counter race conditions | Phase 3 (Hardening) | Like/unlike wrapped in DB transaction; or replaced with `withCount` |
| Admin email bypass | Phase 1 (Foundation) | AdminMiddleware test verifies non-admin with that email is rejected |

## Sources

- Codebase analysis: `FriendController.php`, `ReelController.php`, `SuggestionController.php`, `SettingsController.php`, `UserFactory.php`, `routes/api.php`, all API Resource classes
- [Anthropic API Errors Documentation](https://platform.claude.com/docs/en/api/errors) -- HTTP error codes, rate limits, timeout guidance, retry strategies
- [Laravel Claude API Integration Guide](https://origin-main.com/guides/laravel-claude-api-integration-guide/) -- service layer patterns, timeout configuration
- [Complete Guide to Integrating Claude API with Laravel](https://dev.to/dewald_hugo_472be9f413c2a/the-complete-guide-to-integrating-claude-api-with-laravel-5413) -- error handling, DI patterns
- [Laravel API Development Best Practices 2025](https://hafiz.dev/blog/laravel-api-development-restful-best-practices-for-2025) -- API resource contracts, response consistency
- [10 Laravel REST API Best Practices 2026](https://benjamincrozat.com/laravel-restful-api-best-practices) -- resource design, breaking change prevention
- `.planning/codebase/CONCERNS.md` -- pre-identified tech debt, bugs, and security issues

---
*Pitfalls research for: Laravel API backend parity and hardening*
*Researched: 2026-03-27*
