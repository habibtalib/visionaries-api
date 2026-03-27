# Roadmap: Visionaries API v1.2

## Overview

This milestone brings the Visionaries API backend to full parity with the React frontend. The backend is structurally complete -- controllers exist for Friends, Quizzes, and Reels but lack routes, backing models, and migrations. The work proceeds in strict dependency order: fix foundations (models, migrations, factories, bugs), wire routes for three feature areas, add new endpoints, integrate Claude AI for suggestions, then harden response contracts, validation, and test coverage. The goal: every one of the frontend's 70+ API calls hits a working, well-tested endpoint.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [ ] **Phase 1: Foundation** - Models, migrations, factories, and bug fixes that unblock everything else
- [ ] **Phase 2: Friends System** - Wire friend routes and fix controller bugs for the complete friends feature
- [ ] **Phase 3: Quizzes and Reels** - Wire quiz and reel routes with backing model for reel interactions
- [ ] **Phase 4: New Endpoints** - Action streaks, journal sharing, and settings endpoints
- [ ] **Phase 5: AI Suggestions** - Claude API integration for personalized suggestion generation
- [ ] **Phase 6: API Resource Hardening** - Replace pass-through resources with explicit field mappings
- [ ] **Phase 7: Validation Hardening** - Form Request classes and consistent error responses
- [ ] **Phase 8: Test Suite** - Feature tests for all critical paths across every feature area

## Phase Details

### Phase 1: Foundation
**Goal**: All models, migrations, and factories exist and work correctly so that subsequent phases can route controllers without fatal errors
**Depends on**: Nothing (first phase)
**Requirements**: FND-01, FND-02, FND-03, FND-04, FND-05, FND-06
**Success Criteria** (what must be TRUE):
  1. Friend model exists with a `friends` table migration (user_id, friend_id, status, timestamps) and `php artisan migrate` succeeds
  2. ReelInteraction model exists with migration (user_id, reel_id, type, timestamps) and `php artisan migrate` succeeds
  3. UserFactory creates users without error (uses `display_name` not `name`)
  4. Model factories exist for Action, Friend, Reel, Quiz, JournalEntry, CheckIn, CommunityPost and each can create a record without error
  5. No `env()` calls remain in service classes -- all use `config()` references
**Plans**: TBD

Plans:
- [ ] 01-01: TBD
- [ ] 01-02: TBD

### Phase 2: Friends System
**Goal**: Users can manage friendships end-to-end -- search, request, accept, decline, remove, and view friend profiles
**Depends on**: Phase 1 (Friend model and migration must exist)
**Requirements**: FRD-01, FRD-02, FRD-03, FRD-04, FRD-05, FRD-06, FRD-07, FRD-08, FRD-09
**Success Criteria** (what must be TRUE):
  1. User can search for other users by name and see results (GET /friends/search returns matching users)
  2. User can send a friend request, and the recipient sees it in their pending requests list
  3. User can accept or decline a pending request, and both users' friend lists update accordingly
  4. User can remove an existing friend, and both users' friend lists reflect the removal
  5. Sending a friend request to yourself or to someone you already requested returns an error message
**Plans**: TBD

Plans:
- [ ] 02-01: TBD

### Phase 3: Quizzes and Reels
**Goal**: Users can take quizzes and browse reels -- two content features the frontend already has pages for
**Depends on**: Phase 1 (ReelInteraction model must exist)
**Requirements**: QIZ-01, QIZ-02, QIZ-03, QIZ-04, REL-01, REL-02, REL-03
**Success Criteria** (what must be TRUE):
  1. User can list all available quizzes and view a specific quiz with its questions
  2. User can submit quiz answers and receive a scored result
  3. User can view their quiz attempt history
  4. User can browse reels with pagination (GET /reels returns paginated results)
  5. User can like/unlike and save/unsave a reel with toggle behavior (second call reverses the first)
**Plans**: TBD

Plans:
- [ ] 03-01: TBD

### Phase 4: New Endpoints
**Goal**: Remaining frontend API calls for action streaks, journal sharing, and settings all hit working endpoints
**Depends on**: Phase 1 (factories and fixes)
**Requirements**: ACT-01, JNL-01, SET-01, SET-02
**Success Criteria** (what must be TRUE):
  1. User can view streak data for any action showing current streak, longest streak, and total completions
  2. User can share a journal entry and receive a shareable reference
  3. User can export all their personal data (vision, traits, actions, journal, check-ins, reviews, timeline) as a downloadable file
  4. User can update their locale preference via the settings endpoint and subsequent responses respect the new locale
**Plans**: TBD

Plans:
- [ ] 04-01: TBD

### Phase 5: AI Suggestions
**Goal**: Users receive personalized Islamic development suggestions generated by Claude AI based on their vision, traits, and actions
**Depends on**: Phase 1 (Suggestion model and migration)
**Requirements**: AIS-01, AIS-02, AIS-03, AIS-04, AIS-05
**Success Criteria** (what must be TRUE):
  1. Suggestions table exists with seeded data replacing the hardcoded suggestions array
  2. User can call POST /suggestions/generate and receive personalized suggestions based on their profile data
  3. Repeated calls within a caching window return the same suggestions without making additional Claude API calls
  4. When the Claude API is unavailable (timeout, rate limit, overload), the user receives a clear error message instead of a 500 error
**Plans**: TBD

Plans:
- [ ] 05-01: TBD

### Phase 6: API Resource Hardening
**Goal**: All API responses use explicit field mappings with no sensitive data leakage and consistent formatting
**Depends on**: Phases 2-5 (all routes must be stable before refactoring response shapes)
**Requirements**: RES-01, RES-02, RES-03
**Success Criteria** (what must be TRUE):
  1. No API Resource class uses `parent::toArray()` -- every resource explicitly lists its fields
  2. No API response includes `password`, `remember_token`, or other sensitive fields
  3. All date fields across all endpoints use ISO 8601 format
  4. Relationship data is loaded via `whenLoaded()` and does not trigger N+1 queries
**Plans**: TBD

Plans:
- [ ] 06-01: TBD

### Phase 7: Validation Hardening
**Goal**: All endpoints with complex input use Form Request classes with consistent error formatting
**Depends on**: Phases 2-5 (all routes must be stable before extracting validation)
**Requirements**: VAL-01, VAL-02
**Success Criteria** (what must be TRUE):
  1. All endpoints accepting user input use Form Request classes instead of inline `$request->validate()`
  2. Every validation failure returns the same JSON error structure regardless of which endpoint triggered it
  3. Form Request `authorize()` methods enforce ownership checks (e.g., user can only edit their own actions, journal entries)
**Plans**: TBD

Plans:
- [ ] 07-01: TBD

### Phase 8: Test Suite
**Goal**: Critical paths across every feature area have feature tests that catch regressions
**Depends on**: Phases 1-7 (tests validate everything above)
**Requirements**: TST-01, TST-02, TST-03, TST-04, TST-05, TST-06
**Success Criteria** (what must be TRUE):
  1. `composer test` passes with zero failures
  2. Authentication flow is tested (login, register, logout, Google OAuth token exchange)
  3. Friends system is tested (all 8 endpoints plus duplicate/self-request edge cases)
  4. Quizzes and Reels are tested (list, show, submit, history, like/save toggle)
  5. AI suggestion generation is tested (success path, error handling, caching behavior)
**Plans**: TBD

Plans:
- [ ] 08-01: TBD

## Progress

**Execution Order:**
Phases execute in numeric order: 1 -> 2 -> 3 -> 4 -> 5 -> 6 -> 7 -> 8

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Foundation | 0/0 | Not started | - |
| 2. Friends System | 0/0 | Not started | - |
| 3. Quizzes and Reels | 0/0 | Not started | - |
| 4. New Endpoints | 0/0 | Not started | - |
| 5. AI Suggestions | 0/0 | Not started | - |
| 6. API Resource Hardening | 0/0 | Not started | - |
| 7. Validation Hardening | 0/0 | Not started | - |
| 8. Test Suite | 0/0 | Not started | - |
