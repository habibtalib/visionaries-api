# Requirements: Visionaries API v1.2

**Defined:** 2026-03-27
**Core Value:** Every frontend API call hits a working, complete, and well-tested backend endpoint — zero 404s, zero mock data, zero broken features.

## v1.2 Requirements

### Foundation

- [ ] **FND-01**: Friend model and migration created with friends table (user_id, friend_id, status, timestamps)
- [ ] **FND-02**: ReelInteraction model and migration created (user_id, reel_id, type, timestamps)
- [ ] **FND-03**: UserFactory fixed to use `display_name` instead of `name` to match users table schema
- [ ] **FND-04**: FriendController `ilike` replaced with `LIKE` for SQLite compatibility
- [ ] **FND-05**: All `env()` calls in services replaced with `config()` for production cache compatibility
- [ ] **FND-06**: Model factories created for Action, Friend, Reel, Quiz, JournalEntry, CheckIn, CommunityPost, and other testable models

### Friends

- [ ] **FRD-01**: User can view their accepted friends list (GET /friends)
- [ ] **FRD-02**: User can view pending friend requests (GET /friends/requests)
- [ ] **FRD-03**: User can search for other users by name (GET /friends/search)
- [ ] **FRD-04**: User can view a friend's profile with their public data (GET /friends/{id}/profile)
- [ ] **FRD-05**: User can send a friend request to another user (POST /friends/request)
- [ ] **FRD-06**: User can accept a pending friend request (POST /friends/{id}/accept)
- [ ] **FRD-07**: User can decline a pending friend request (POST /friends/{id}/decline)
- [ ] **FRD-08**: User can remove an existing friend (DELETE /friends/{id})
- [ ] **FRD-09**: Duplicate and self-friend requests are prevented with proper error messages

### Quizzes

- [ ] **QIZ-01**: User can view all available quizzes (GET /quizzes)
- [ ] **QIZ-02**: User can view a specific quiz with its questions (GET /quizzes/{id})
- [ ] **QIZ-03**: User can submit quiz answers and receive scored results (POST /quizzes/submit)
- [ ] **QIZ-04**: User can view their quiz attempt history (GET /quizzes/history)

### Reels

- [ ] **REL-01**: User can browse reels with pagination (GET /reels)
- [ ] **REL-02**: User can like/unlike a reel with toggle behavior (POST /reels/{id}/like)
- [ ] **REL-03**: User can save/unsave a reel with toggle behavior (POST /reels/{id}/save)

### Actions

- [ ] **ACT-01**: User can view streak data for a specific action (GET /actions/{id}/streak) showing current streak, longest streak, and total completions

### Journal

- [ ] **JNL-01**: User can share a journal entry (POST /journal/{id}/share) generating a shareable reference

### Settings

- [ ] **SET-01**: User can export their personal data as a downloadable file (GET /settings/export) including vision, traits, actions, journal entries, check-ins, reviews, and timeline
- [ ] **SET-02**: User can update their locale preference via settings (PUT /settings/locale)

### AI Suggestions

- [ ] **AIS-01**: Suggestions stored in database table with seeder replacing hardcoded array
- [ ] **AIS-02**: Suggestion model with UUID, category, title, description, domain mapping, impact, effort fields
- [ ] **AIS-03**: User can generate personalized AI suggestions via Claude API (POST /suggestions/generate) based on their vision, traits, and current actions
- [ ] **AIS-04**: AI-generated suggestions cached per user to avoid redundant API calls
- [ ] **AIS-05**: Claude API errors (rate limits, timeouts, overload) handled gracefully with user-friendly error responses

### API Resources

- [ ] **RES-01**: All 13 API Resource classes replaced with explicit field mappings (no more `parent::toArray()` pass-throughs)
- [ ] **RES-02**: Consistent date formatting (ISO 8601) across all resource responses
- [ ] **RES-03**: Relationships loaded via `whenLoaded()` to prevent N+1 queries in resource responses

### Validation

- [ ] **VAL-01**: Form Request classes created for all endpoints with complex validation (replacing inline `$request->validate()`)
- [ ] **VAL-02**: Consistent error response format across all validation failures

### Testing

- [ ] **TST-01**: Feature tests for authentication flow (login, register, logout, Google OAuth)
- [ ] **TST-02**: Feature tests for Friends system (all 8 endpoints including edge cases)
- [ ] **TST-03**: Feature tests for Quizzes system (list, show, submit, history)
- [ ] **TST-04**: Feature tests for Reels system (list, like toggle, save toggle)
- [ ] **TST-05**: Feature tests for AI suggestion generation (success, error handling, caching)
- [ ] **TST-06**: Feature tests for new endpoints (streak, journal share, settings export/locale)

## v1.3 Requirements

### Deferred

- **DEF-01**: API versioning (v1/v2 URL prefixing)
- **DEF-02**: Global exception handler customization
- **DEF-03**: Request/response logging middleware
- **DEF-04**: API documentation generation (OpenAPI/Swagger)
- **DEF-05**: Database migration to PostgreSQL/MySQL

## Out of Scope

| Feature | Reason |
|---------|--------|
| Frontend changes | Backend-only milestone; frontend is a separate repo |
| Admin panel expansion | Current Filament resources are sufficient |
| Email notification templates | No email delivery configured yet |
| Payment/subscription features | No monetization in roadmap |
| Rate limiting changes | Current limits adequate (60/min API, 5/min auth) |
| Dark mode API support | Theme handled client-side |
| User-generated reels upload | Content moderation burden; admin-managed only |
| Real-time chat/messaging | High complexity; not in current product scope |

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| FND-01 | TBD | Pending |
| FND-02 | TBD | Pending |
| FND-03 | TBD | Pending |
| FND-04 | TBD | Pending |
| FND-05 | TBD | Pending |
| FND-06 | TBD | Pending |
| FRD-01 | TBD | Pending |
| FRD-02 | TBD | Pending |
| FRD-03 | TBD | Pending |
| FRD-04 | TBD | Pending |
| FRD-05 | TBD | Pending |
| FRD-06 | TBD | Pending |
| FRD-07 | TBD | Pending |
| FRD-08 | TBD | Pending |
| FRD-09 | TBD | Pending |
| QIZ-01 | TBD | Pending |
| QIZ-02 | TBD | Pending |
| QIZ-03 | TBD | Pending |
| QIZ-04 | TBD | Pending |
| REL-01 | TBD | Pending |
| REL-02 | TBD | Pending |
| REL-03 | TBD | Pending |
| ACT-01 | TBD | Pending |
| JNL-01 | TBD | Pending |
| SET-01 | TBD | Pending |
| SET-02 | TBD | Pending |
| AIS-01 | TBD | Pending |
| AIS-02 | TBD | Pending |
| AIS-03 | TBD | Pending |
| AIS-04 | TBD | Pending |
| AIS-05 | TBD | Pending |
| RES-01 | TBD | Pending |
| RES-02 | TBD | Pending |
| RES-03 | TBD | Pending |
| VAL-01 | TBD | Pending |
| VAL-02 | TBD | Pending |
| TST-01 | TBD | Pending |
| TST-02 | TBD | Pending |
| TST-03 | TBD | Pending |
| TST-04 | TBD | Pending |
| TST-05 | TBD | Pending |
| TST-06 | TBD | Pending |

**Coverage:**
- v1.2 requirements: 42 total
- Mapped to phases: 0
- Unmapped: 42

---
*Requirements defined: 2026-03-27*
*Last updated: 2026-03-27 after initial definition*
