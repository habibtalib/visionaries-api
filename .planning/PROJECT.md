# Visionaries API — Backend Parity & Hardening

## What This Is

The Laravel 12 REST API backend for Visionaries, an Islamic personal development mobile app. The frontend (React + TypeScript) is fully built through v1.1 with 25 pages and 70+ API calls — but several controller routes are missing, some endpoints don't exist yet, and the codebase lacks tests and consistent response formatting. This milestone brings the backend to full parity with the frontend and hardens it for production.

## Core Value

Every frontend API call hits a working, complete, and well-tested backend endpoint — zero 404s, zero mock data, zero broken features.

## Requirements

### Validated

- ✓ Authentication (email/password + Google OAuth via Sanctum + Socialite) — existing
- ✓ Vision CRUD with version history and VisionUpdated broadcast — existing
- ✓ Traits library with user selection (max 7), custom traits, localization — existing
- ✓ Actions CRUD with domain filtering, daily tracking, check-ins — existing
- ✓ Journal entries CRUD with pagination — existing
- ✓ Daily check-ins (spiritual) with upsert on date — existing
- ✓ Timeline activity feed with category filtering and pagination — existing
- ✓ Community feeds with posts, likes (toggle), comments — existing
- ✓ Reviews (weekly/monthly/quarterly/annual) with upsert — existing
- ✓ Islamic calendar events endpoint — existing
- ✓ Push notifications (VAPID Web Push + OneSignal) with subscribe/unsubscribe — existing
- ✓ Admin broadcast push notifications with stats — existing
- ✓ Dashboard today endpoint — existing
- ✓ Language switching with locale persistence — existing
- ✓ Real-time broadcasting via Reverb (user, friends, community channels) — existing
- ✓ Filament v5 admin panel at /admin — existing
- ✓ Scheduled push commands (morning, evening, weekly) — existing

### Active

**Current Milestone: v1.2 Backend Parity & Hardening**

**Missing routes (controllers exist but aren't routed):**
- [ ] Friends system — index, requests, search, profile, send/accept/decline request, remove
- [ ] Quizzes — index, show with questions, submit answers, attempt history
- [ ] Reels — index with pagination, like toggle, save toggle

**Missing endpoints (no controller method exists):**
- [ ] Action streak data — GET /actions/{id}/streak
- [ ] Journal entry sharing — POST /journal/{id}/share
- [ ] AI suggestion generation — POST /suggestions/generate (real Claude API integration)
- [ ] Settings data export — GET /settings/export
- [ ] Settings locale update — PUT /settings/locale

**Hardening:**
- [ ] API Resource classes — replace pass-through resources with proper field transformations
- [ ] Feature tests for critical paths — Auth, Friends, Quizzes, Reels, new endpoints
- [ ] Move hardcoded suggestions to database with seeder
- [ ] Consistent response format across all endpoints

### Out of Scope

- Frontend changes — this is backend-only work
- Database migration from SQLite to PostgreSQL/MySQL — separate infrastructure concern
- API versioning (v1/v2 prefixing) — premature; single consumer app
- Admin panel feature expansion — current Filament resources are sufficient
- Rate limiting changes — current limits (60/min API, 5/min auth) are adequate
- Email/notification templates — not needed until email delivery is configured
- Payment/subscription features — no monetization in current roadmap
- Dark mode API support — frontend handles theme client-side

## Context

- **Frontend state:** visionaries-pro has shipped v1.0 (5 phases) and v1.1 (7 phases), currently in v2.0 design overhaul (Phase 16 of 18 complete). All 25 pages expect working API endpoints.
- **Existing backend:** 19 controllers, 18 models, 13 resource classes, 5 broadcast events. Most CRUD is functional but Friends/Quizzes/Reels controllers are written but never routed.
- **Tech stack:** Laravel 12, PHP 8.4, Sanctum, Socialite, Reverb, Filament v5, SQLite, minishlink/web-push
- **AI integration:** Anthropic Claude API for personalized suggestion generation based on user's vision, traits, and actions
- **Known tech debt:** Pass-through API resources, hardcoded suggestions array, no Form Request classes (inline validation only), minimal test coverage

## Constraints

- **Tech stack**: Laravel 12 + Sanctum + Reverb — no framework changes
- **Database**: SQLite (development), SQLite in-memory (tests) — migrations must be compatible
- **API contract**: Must match what the React frontend already calls — endpoint paths, request/response shapes are fixed by the frontend code
- **UUID primary keys**: All models use HasUuid trait — string IDs everywhere
- **i18n**: Responses must support en/ms localization where applicable (traits, events)
- **No breaking changes**: Existing working endpoints must not change behavior

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Route existing controllers before writing new ones | Friends/Quizzes/Reels controllers already exist — just need routes added | — Pending |
| Claude API for suggestion generation | User preference; strong reasoning for personalized Islamic development advice | — Pending |
| Feature tests for critical paths only | Full coverage is overkill for current stage; focus on newly routed and new endpoints | — Pending |
| Move suggestions to database | Hardcoded array limits scalability and admin management | — Pending |
| Match frontend response shapes exactly | Frontend is already shipped — backend must conform to what frontend expects | — Pending |

## Evolution

This document evolves at phase transitions and milestone boundaries.

**After each phase transition** (via `/gsd:transition`):
1. Requirements invalidated? → Move to Out of Scope with reason
2. Requirements validated? → Move to Validated with phase reference
3. New requirements emerged? → Add to Active
4. Decisions to log? → Add to Key Decisions
5. "What This Is" still accurate? → Update if drifted

**After each milestone** (via `/gsd:complete-milestone`):
1. Full review of all sections
2. Core Value check — still the right priority?
3. Audit Out of Scope — reasons still valid?
4. Update Context with current state

---
*Last updated: 2026-03-27 after initialization*
