# Feature Landscape

**Domain:** Islamic personal development app API (social + habit tracking + content + AI)
**Researched:** 2026-03-27

## Table Stakes

Features users expect. Missing = product feels incomplete or broken (frontend already calls these).

### 1. Friend System (Social)

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| Friend list (accepted) | Core social feature; frontend page exists | Low | Controller exists, needs route + `friends` migration |
| Pending requests (inbox) | Users need to see and act on requests | Low | Controller exists, needs route |
| Send friend request | Initiating connections is fundamental | Low | Controller exists but references missing `FriendRequestReceived` event import and `FriendRequestNotification` |
| Accept/decline request | Request flow is incomplete without response actions | Low | Controller exists, needs route |
| Remove friend | Users must be able to undo connections | Low | Controller exists, needs route |
| User search | Cannot add friends without finding them | Low | Controller exists; uses `ilike` which fails on SQLite -- needs `like` instead |
| Friend profile view | Tapping a friend should show their profile | Low | Controller exists, needs route |
| Duplicate request prevention | Sending duplicate requests breaks UX and creates data inconsistency | Low | Missing: no unique constraint or check in `sendRequest` |
| Self-request prevention | Users should not be able to friend themselves | Low | Missing: no validation in `sendRequest` |
| Block already-friends | Should not send request to existing friend | Low | Missing: no check in `sendRequest` |

### 2. Quiz/Assessment System

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| Quiz listing | Frontend needs to display available quizzes | Low | Controller exists, needs route |
| Quiz submission with scoring | Core quiz functionality | Low | Controller exists, needs route; scoring logic is inline |
| Attempt history | Users expect to see past results | Low | Controller exists, needs route |
| Show single quiz with questions | Frontend quiz detail page needs this | Low | Missing: no `show($id)` method on QuizController |
| Per-quiz attempt history | View attempts for a specific quiz, not just all attempts | Low | Missing: controller only has global history |

### 3. Content Reels/Feed

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| Reel listing (paginated, random) | Frontend reel swiping page exists | Low | Controller exists, needs route |
| Like toggle | Standard content interaction | Low | Controller exists, needs route + `ReelInteraction` model + migration |
| Save/bookmark toggle | Standard content interaction | Low | Controller exists, needs route + same migration as like |
| User's liked/saved state per reel | Frontend needs to show filled/unfilled heart/bookmark | Med | Missing: controller returns `liked`/`saved` boolean but index doesn't include user interaction state |
| Localized content | Reel model has `content_ms` but no localization accessor | Low | Missing: needs `getLocalizedContentAttribute()` like `Trait_` model |

### 4. Action Streak Data

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| Current streak count | Primary motivational metric in habit apps | Med | Missing: no `streak` method on ActionController; requires date-gap calculation from `action_check_ins` |
| Longest streak | Users want to know their personal best | Med | Same calculation, track max |
| Streak per action | Frontend calls `GET /actions/{id}/streak` | Med | Must query `action_check_ins` for specific action, ordered by date, count consecutive |
| Weekly/monthly completion rate | Context for streak; "you completed 5/7 days this week" | Med | Missing: no stats endpoint scoped to time periods |

### 5. Settings Locale Update

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| PUT /settings/locale | Frontend settings page calls this | Low | Controller exists (`SettingsController::updateLocale`), needs route |

### 6. Settings Data Export

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| GET /settings/export | User data portability; GDPR-adjacent compliance | Med | Controller exists with basic implementation; needs reviews, check-ins, community posts, timeline events added |
| JSON format | Machine-readable, preserves relationships | Low | Already implemented as JSON response |
| Downloadable file response | Users expect a file download, not raw JSON in browser | Low | Missing: should return with `Content-Disposition: attachment` header |

### 7. Journal Entry Sharing

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| POST /journal/{id}/share | Frontend has share button | Med | Missing: no method on JournalController; needs to define what "share" means (generate shareable link? share to community? share to friends?) |

### 8. Consistent API Resources

| Feature | Why Expected | Complexity | Notes |
|---------|--------------|------------|-------|
| Proper field transformations in resources | Frontend expects consistent shapes; pass-through resources leak internal model structure | Med | 13 resource classes exist but most use `parent::toArray()` |
| Consistent response envelope | Frontend parsing expects predictable structure | Med | Mix of `response()->json($data)` and `Resource::collection()` patterns |


## Differentiators

Features that set the product apart. Not expected in every app, but high value for an Islamic personal development context.

| Feature | Value Proposition | Complexity | Notes |
|---------|-------------------|------------|-------|
| AI suggestion generation (Claude API) | Personalized action recommendations based on user's vision, traits, and current actions; deeply contextual Islamic advice | High | Frontend calls `POST /suggestions/generate`; currently hardcoded array; Claude API integration needed |
| Streak "mercy" mechanic | Instead of hard reset on miss, use habit-strength scoring (research shows this reduces abandonment by preventing "What the Hell Effect") | Med | Not in frontend yet but a strong UX improvement over simple streak counting |
| Friend activity visibility | See friends' streak counts or recent completions on friend profile (accountability partner pattern) | Med | Requires friend profile endpoint to include activity summary |
| Pillar-based quiz categorization | Quizzes organized by SEE/BE/DO pillar for targeted self-assessment | Low | `pillar` column exists on quizzes table; just needs frontend surfacing |
| Real-time friend notifications | Broadcast `FriendRequestReceived` event via Reverb when request is sent | Low | Event class exists; just needs proper import and channel authorization |
| Suggestion refinement with user context | Pass user's vision statement, selected traits, existing actions, and recent journal themes to Claude for genuinely personalized suggestions | High | This is the core differentiator; generic suggestions are table stakes, personalized ones are the moat |


## Anti-Features

Features to explicitly NOT build in this milestone.

| Anti-Feature | Why Avoid | What to Do Instead |
|--------------|-----------|-------------------|
| Real-time chat / messaging | Massive complexity (message storage, read receipts, typing indicators, moderation); not core to personal development | Keep friend system as accountability only; community posts handle async social |
| Gamification leaderboards | Public ranking creates unhealthy competition in a spiritual development context; research shows streak notifications beyond 2/week increase abandonment | Show personal progress only; friend streaks visible only on direct profile view |
| Public user profiles | Privacy concern for a faith-based app; users may not want spiritual journey visible to strangers | Friends-only profile visibility; search returns minimal info (name, avatar) |
| Complex quiz branching / adaptive testing | Over-engineering for a supplementary feature; quizzes are knowledge checks, not assessments | Simple question list with correct/incorrect scoring is sufficient |
| Rich media in reels (video/audio) | Storage cost, CDN complexity, content moderation burden | Text-based inspirational content with gradients (current model) is intentional and lightweight |
| Social login beyond Google | Each OAuth provider adds maintenance burden; Apple login requires paid dev account | Google SSO covers primary use case; email/password as fallback |
| Offline sync / conflict resolution | Enormous complexity for edge cases; mobile app can cache reads locally | Frontend handles read caching; writes require connectivity |
| GraphQL API | Single consumer (one mobile app); REST is simpler and already implemented across 70+ endpoints | REST with consistent resources; no multi-client query flexibility needed |
| Payment/subscription system | Out of scope per PROJECT.md; no monetization in current roadmap | Defer entirely |


## Feature Dependencies

```
friends migration        --> Friend list, requests, send/accept/decline, remove, search, profile
ReelInteraction model    --> Reel like toggle, save toggle
ReelInteraction migration --> ReelInteraction model
Reel user state in index --> ReelInteraction model (needs to exist first)

Friend routes            --> FriendRequestReceived event import fix
                         --> FriendRequestNotification class (does not exist yet)

Streak calculation       --> action_check_ins table (exists)
                         --> Date-gap algorithm (new logic)

AI suggestions           --> Claude API key in .env
                         --> User vision, traits, actions loaded as context
                         --> Queue worker (for async generation)
                         --> Suggestion model + migration (to persist generated suggestions)

Journal sharing          --> Define sharing semantics first (link? community post? friend share?)
                         --> Potentially community_posts integration

Data export completeness --> All user-related models must be included
                         --> Downloadable JSON file response

Consistent API resources --> Must audit all 13 resources
                         --> Must not break existing frontend response shapes
```

## MVP Recommendation

**Priority order based on frontend parity (things already called by the app):**

1. **Route existing controllers first** (Friends, Quizzes, Reels) -- lowest effort, highest impact. These controllers are written; they just need `routes/api.php` entries plus missing migrations/models.

2. **Create missing migrations** -- `friends` table, `reel_interactions` table. Without these, three entire features are non-functional.

3. **Fix controller bugs before routing** -- `FriendController::search` uses `ilike` (PostgreSQL syntax, fails on SQLite), `sendRequest` has no duplicate/self-request guards, missing model imports.

4. **Add streak endpoint** -- `GET /actions/{id}/streak`. High-visibility frontend feature; calculation is straightforward from existing `action_check_ins` data.

5. **Route settings endpoints** -- `PUT /settings/locale` and `GET /settings/export`. Controller methods exist; just need routes.

6. **Add reel user interaction state** -- Modify reel index to include `is_liked` and `is_saved` per user. Frontend needs this for toggle state.

7. **Define and implement journal sharing** -- Needs design decision on what "share" means before implementation.

8. **AI suggestion generation** -- Highest complexity; defer to last phase. Requires Claude API integration, prompt engineering, queue handling, and a new `suggestions` table. Current hardcoded array works as a stopgap.

**Defer to future milestone:**
- Streak "mercy" mechanic (needs frontend support, UX design decision)
- Friend activity on profile (nice-to-have, not called by frontend yet)
- Per-quiz attempt filtering (frontend may not need it yet)

## Complexity Summary

| Feature Area | Total Items | Low | Med | High |
|-------------|-------------|-----|-----|------|
| Friend System | 10 | 9 | 0 | 1 (migration creation) |
| Quiz System | 5 | 4 | 0 | 1 (missing show method) |
| Reels | 5 | 3 | 2 | 0 |
| Streaks | 4 | 0 | 4 | 0 |
| Settings | 3 | 2 | 1 | 0 |
| Journal Sharing | 1 | 0 | 1 | 0 |
| AI Suggestions | 1 | 0 | 0 | 1 |
| API Resources | 2 | 0 | 2 | 0 |

## Sources

- Codebase analysis: `routes/api.php`, all controllers in `app/Http/Controllers/Api/`, all models, all migrations
- [ICO - Right to data portability](https://ico.org.uk/for-organisations/uk-gdpr-guidance-and-resources/individual-rights/individual-rights/right-to-data-portability/) (GDPR export format guidance)
- [Habi - Best Streak Tracker Apps 2026](https://habi.app/insights/best-streak-tracker-apps/) (streak mechanic patterns)
- [ClickUp - Best Habit Tracker Apps 2026](https://clickup.com/blog/best-habit-tracker-app/) (habit strength scoring vs hard streak reset)
- [DEV Community - Claude API with Laravel](https://dev.to/dewald_hugo_472be9f413c2a/the-complete-guide-to-integrating-claude-api-with-laravel-5413) (Laravel + Claude integration patterns)
- [Laravel AI Documentation](https://laravel.com/docs/12.x/ai) (Laravel 12 AI-assisted development)
- [Origin Main - Claude API Laravel Guide](https://origin-main.com/guides/the-complete-guide-to-integrating-claude-api-with-laravel/) (RAG pattern, queue architecture for AI workers)

---

*Feature landscape analysis: 2026-03-27*
