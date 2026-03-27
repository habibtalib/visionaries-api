# Project Research Summary

**Project:** Visionaries API Backend Hardening
**Domain:** Laravel 12 REST API -- Islamic personal development mobile app backend
**Researched:** 2026-03-27
**Confidence:** HIGH

## Executive Summary

Visionaries API is a Laravel 12 backend that is structurally complete but operationally broken. Controllers exist for Friends, Quizzes, and Reels but have no routes, no backing models/migrations, and reference non-existent classes. All 13 API Resources use pass-through `parent::toArray()` which leaks sensitive fields including password hashes. There are zero tests, a broken UserFactory, and several controllers with SQLite-incompatible queries and wrong column names. The codebase needs systematic hardening before any new features can be safely added.

The recommended approach is a strict dependency-ordered build: fix foundations first (models, migrations, factories, known bugs), then wire routes, then add the one genuinely new capability (Claude AI suggestion generation), then harden response contracts and validation, and finally bootstrap a test suite. This order is non-negotiable because routing controllers that reference non-existent models causes fatal errors, and refactoring API resources without tests risks silently breaking the frontend's 70+ API calls.

The primary risks are: (1) breaking the frontend when refactoring pass-through resources into explicit field maps -- mitigated by writing tests that lock down response shapes before touching resources; (2) Claude API integration without proper timeout/retry/cost controls -- mitigated by a dedicated service class with 60-second timeout, retry on 429/529, and per-user rate limiting; (3) `env()` calls in service classes that return null in production when config is cached -- mitigated by moving all credentials to `config/services.php` as a foundation fix.

## Key Findings

### Recommended Stack

The existing stack (Laravel 12, Sanctum, Reverb, Filament v5) requires only one new dependency: `mozex/anthropic-laravel` v1.3+ for Claude API integration. This package provides a Laravel Facade, publishable config, and `Anthropic::fake()` for testing. It was chosen over `prism-php/prism` (unnecessary multi-provider abstraction), `laravel/ai` (still v0.4, pre-1.0), and raw HTTP calls (no retry/streaming/test faking). PHPUnit stays as-is -- migrating to Pest adds churn with zero existing tests to maintain.

**Core technologies:**
- `mozex/anthropic-laravel` ^1.3: Claude API integration -- Laravel Facade, config, test faking, covers messages/streaming/tool use
- PHPUnit 11.5 (existing): Feature testing -- already installed, no migration needed
- Laravel Pint (existing): Code formatting -- PSR-12 consistency

### Expected Features

**Must have (table stakes -- frontend already calls these):**
- Friend system (list, requests, send/accept/decline, remove, search, profile) -- controllers exist, need models + migrations + routes
- Quiz system (listing, submission with scoring, attempt history) -- controllers exist, need routes
- Reels (listing, like/save toggle, user interaction state) -- controllers exist, need model + migration + routes
- Action streak calculation (current streak, longest streak, per-action) -- new logic against existing `action_check_ins` table
- Settings endpoints (locale update, data export) -- controllers exist, need routes
- Consistent API Resources with explicit field mapping -- 13 resources need refactoring from pass-through

**Should have (differentiators):**
- AI suggestion generation via Claude API -- personalized recommendations based on vision, traits, and actions; the core product differentiator
- Suggestion refinement with full user context -- transforms generic suggestions into genuinely personalized Islamic guidance
- Real-time friend notifications via Reverb -- event class exists, needs import fix and channel authorization

**Defer (v2+):**
- Streak "mercy" mechanic (needs frontend UX design)
- Friend activity on profile (not called by frontend yet)
- Journal sharing semantics (needs design decision on what "share" means)

### Architecture Approach

The architecture is a layered MVC API monolith that needs completion, not redesign. The target state adds Form Request classes for validation, a service layer for external APIs (ClaudeService), and explicit API Resources. The build order follows strict dependency chains: migrations/models first, then routes, then services, then response hardening, then tests last (since tests validate everything above).

**Major components:**
1. **Foundation layer** (migrations, models, factories) -- Friend, ReelInteraction, Suggestion models with UUID keys; fixed UserFactory
2. **Route layer** -- wire 3 unrouted controller groups (Friends, Quizzes, Reels) plus Settings and Suggestions endpoints
3. **Service layer** -- ClaudeService for AI integration with timeout, retry, caching, and cost controls
4. **Response layer** -- all 13 API Resources refactored to explicit field maps with `whenLoaded()` for relationships
5. **Validation layer** -- Form Request classes replace inline `$request->validate()` calls
6. **Test layer** -- feature tests organized by domain, exercising full HTTP stack

### Critical Pitfalls

1. **Non-existent models referenced by controllers** -- FriendController and ReelController will fatal error when routed. Create models and migrations BEFORE registering any routes.
2. **Breaking frontend response shapes during resource refactoring** -- pass-through resources created an implicit contract. Write tests encoding current response shapes BEFORE refactoring. Never rename JSON keys.
3. **Broken UserFactory blocks all testing** -- `name` column should be `display_name`. Two-minute fix that unblocks the entire test effort. Fix first.
4. **Claude API without timeout/retry/cost controls** -- AI APIs are slow (10-60s), fail transiently (429/529), and cost per-token. Dedicated service class with 60s timeout, 3 retries, per-user rate limiting, and response caching is mandatory.
5. **`env()` calls in services break production** -- existing bug in PushNotificationService/OneSignalService. All credentials must go through `config/services.php`. Fix existing services AND ensure ClaudeService uses `config()` from day one.

## Implications for Roadmap

Based on research, suggested phase structure:

### Phase 1: Foundation Fixes
**Rationale:** Everything else depends on working models, factories, and correct column references. This phase has zero risk of breaking the frontend because it touches no existing routes.
**Delivers:** Working Friend model + migration, ReelInteraction model + migration, Suggestion model + migration + seeder, fixed UserFactory, fixed `env()` calls in existing services, fixed column name mismatches (SettingsController, FriendController), missing model relationships (User::communityPosts), admin email bypass removal.
**Addresses:** Table stakes infrastructure for Friends, Reels, and Suggestions features.
**Avoids:** Pitfalls 1 (non-existent models), 3 (broken factory), 5 (hidden broken code), 7 (env() calls).

### Phase 2: Route Wiring and Controller Bug Fixes
**Rationale:** With models in place, controllers can be safely routed. This phase activates 3 entire feature areas the frontend already expects.
**Delivers:** Route groups for Friends, Quizzes, Reels, Settings. FriendController `ILIKE` to `LIKE` fix, duplicate/self-request guards, missing event imports. Streak calculation endpoint.
**Addresses:** All table-stakes features: friend system, quiz system, reels, settings locale/export, action streaks.
**Avoids:** Pitfall 1 (routes referencing missing models -- now resolved by Phase 1).

### Phase 3: AI Suggestion Integration
**Rationale:** Highest complexity feature, isolated from other work. Requires new package installation, service class, prompt engineering, and cost controls. Depends on Suggestion model from Phase 1.
**Delivers:** `mozex/anthropic-laravel` installed, ClaudeService with timeout/retry/caching, `POST /suggestions/generate` endpoint, per-user rate limiting on AI endpoint.
**Uses:** `mozex/anthropic-laravel` ^1.3, `config/services.php` for credentials.
**Avoids:** Pitfall 4 (Claude API without controls), Pitfall 6 (suggestion data migration -- UUID IDs coordinated with frontend).

### Phase 4: API Resource Hardening
**Rationale:** Must come AFTER tests exist for current response shapes (or at minimum, after routes are wired so response shapes can be documented). Refactoring resources without tests risks silent frontend breakage.
**Delivers:** All 13+ API Resources with explicit field maps, `whenLoaded()` for relationships, sensitive field exclusion (password, remember_token), consistent date formatting.
**Addresses:** Table-stakes "consistent API resources" requirement.
**Avoids:** Pitfall 2 (breaking frontend response shapes).

### Phase 5: Validation Hardening
**Rationale:** Can overlap with Phase 4. Form Requests improve code quality but are lower risk than resource refactoring.
**Delivers:** Form Request classes for all endpoints accepting input. `authorize()` methods for ownership checks (friend profile access, action ownership).
**Addresses:** Security (authorization checks), code quality (thin controllers).

### Phase 6: Test Suite Bootstrap
**Rationale:** Tests come last because they validate everything above. Writing tests against broken foundations wastes time debugging pre-existing bugs (which Phase 1 fixes).
**Delivers:** Model factories for all testable models, feature tests organized by domain (Auth, Friends, Quizzes, Reels, Actions, Suggestions, Settings), coverage of critical paths and error cases.
**Addresses:** Zero-test technical debt. Guards against regression for all phases above.

### Phase Ordering Rationale

- Phases 1-2 are strictly sequential: models before routes, no exceptions.
- Phase 3 (AI) can technically start after Phase 1 but is best after Phase 2 so all routes are stable.
- Phases 4-5 (Resources and Validation) can run in parallel and could start as early as after Phase 2.
- Phase 6 (Tests) should run last but individual smoke tests should be written incrementally during earlier phases.
- The overall order prioritizes activating existing broken features before adding new ones, and locks down contracts before refactoring them.

### Research Flags

Phases likely needing deeper research during planning:
- **Phase 3 (AI Integration):** Prompt engineering for Islamic personal development context, Claude API structured output format, token budget estimation, caching strategy for personalized suggestions. The `mozex/anthropic-laravel` API surface needs validation during implementation.
- **Phase 4 (Resource Hardening):** Frontend response contract audit needed -- must document exact fields consumed by each of the 70+ frontend API calls before refactoring.

Phases with standard patterns (skip research-phase):
- **Phase 1 (Foundation):** Standard Laravel migrations, models, factories. Well-documented patterns.
- **Phase 2 (Route Wiring):** Standard Laravel routing. Existing controller code just needs bug fixes.
- **Phase 5 (Validation):** Standard Laravel Form Requests. Official docs are comprehensive.
- **Phase 6 (Tests):** Standard Laravel feature testing with Sanctum. Well-documented.

## Confidence Assessment

| Area | Confidence | Notes |
|------|------------|-------|
| Stack | HIGH | Only one new package needed; verified on Packagist with 350k+ installs; alternatives well-documented |
| Features | HIGH | Grounded in codebase analysis of existing controllers, routes, and frontend API calls |
| Architecture | HIGH | Standard Laravel MVC patterns; no novel architecture decisions needed |
| Pitfalls | HIGH | All pitfalls verified against actual codebase bugs (wrong column names, missing models, env() calls) |

**Overall confidence:** HIGH

### Gaps to Address

- **Frontend response contract:** No formal documentation of what fields the React frontend reads from each endpoint. Must audit frontend before Phase 4 (Resource Hardening) or risk breaking the app. This is the single biggest risk in the project.
- **Journal sharing semantics:** The frontend has a share button but "share" is undefined (shareable link? community post? friend share?). Needs product decision before implementation. Recommend deferring.
- **Claude prompt quality:** No research on optimal prompts for Islamic personal development suggestions. Phase 3 will need iteration and user feedback to get suggestion quality right.
- **Suggestion ID migration:** Frontend may store integer suggestion IDs locally. Need to verify whether the int-to-UUID change requires a coordinated frontend release.

## Sources

### Primary (HIGH confidence)
- Codebase analysis: all controllers, models, routes, resources, migrations, factories
- [Laravel 12.x Eloquent API Resources docs](https://laravel.com/docs/12.x/eloquent-resources)
- [Laravel 12.x HTTP Tests docs](https://laravel.com/docs/12.x/http-tests)
- [Laravel 12.x Testing docs](https://laravel.com/docs/12.x/testing)
- [mozex/anthropic-laravel on Packagist](https://packagist.org/packages/mozex/anthropic-laravel) -- v1.3.3, 350k+ installs
- [anthropic-ai/sdk on Packagist](https://packagist.org/packages/anthropic-ai/sdk) -- v0.8.0, official SDK

### Secondary (MEDIUM confidence)
- [Anthropic API Errors Documentation](https://platform.claude.com/docs/en/api/errors) -- error codes, rate limits, retry strategies
- [DEV Community - Claude API with Laravel](https://dev.to/dewald_hugo_472be9f413c2a/the-complete-guide-to-integrating-claude-api-with-laravel-5413)
- [Origin Main - Claude API Laravel Guide](https://origin-main.com/guides/the-complete-guide-to-integrating-claude-api-with-laravel/)
- [Laravel API Development Best Practices 2025](https://hafiz.dev/blog/laravel-api-development-restful-best-practices-for-2025)

### Tertiary (LOW confidence)
- Habit tracker UX research (streak mechanics, mercy patterns) -- informational only, deferred to v2

---
*Research completed: 2026-03-27*
*Ready for roadmap: yes*
