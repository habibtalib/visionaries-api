# Architecture

**Analysis Date:** 2026-03-27

## Pattern Overview

**Overall:** Layered MVC API + Admin Panel Monolith

**Key Characteristics:**
- Single Laravel application serving both a REST API (consumed by mobile) and a Filament v5 admin panel
- Thin controllers: validation, model interaction, and JSON response — no dedicated service layer for domain logic except push notifications
- All models use UUID primary keys via `App\Traits\HasUuid`
- Domain logic is organized around the SEE/BE/DO framework (Vision, Traits, Actions)

## Layers

**Routing Layer:**
- Purpose: Maps HTTP requests to controllers; defines middleware groups
- Location: `routes/api.php`, `routes/web.php`, `routes/channels.php`
- Contains: Route definitions, middleware application, broadcast channel authorization
- Depends on: Controllers, Middleware
- Used by: Laravel HTTP kernel

**Middleware Layer:**
- Purpose: Cross-cutting concerns applied before controllers execute
- Location: `app/Http/Middleware/`
- Contains:
  - `SetLocale.php` — resolves locale from user preference, query param, Accept-Language header, or default `en`
  - `AdminMiddleware.php` — guards admin API routes by checking `is_admin` flag or hardcoded admin email
- Depends on: Auth, Models
- Used by: Route groups in `bootstrap/app.php` and `routes/api.php`

**Controller Layer:**
- Purpose: Handles HTTP request/response cycle; validates input; orchestrates model operations
- Location: `app/Http/Controllers/Api/`
- Contains: 19 controllers (one per domain area)
- Depends on: Models, Resources, Events, Services
- Used by: Routes

**Model Layer:**
- Purpose: Eloquent ORM models; relationships; attribute accessors; casts
- Location: `app/Models/`
- Contains: 18 models covering all domain entities
- Depends on: `HasUuid` trait, Eloquent
- Used by: Controllers, Resources, Filament Resources, Seeders

**Resource Layer (Response Serialization):**
- Purpose: Transforms Eloquent models into JSON API responses
- Location: `app/Http/Resources/`
- Contains: 13 resource classes extending `JsonResource`
- Depends on: Models
- Used by: Controllers (returned directly as response)

**Service Layer:**
- Purpose: Encapsulates external service integrations
- Location: `app/Services/`
- Contains:
  - `PushNotificationService.php` — VAPID Web Push delivery via `minishlink/web-push`
  - `OneSignalService.php` — OneSignal push notification delivery
- Depends on: Models, external SDKs
- Used by: Controllers

**Event/Broadcasting Layer:**
- Purpose: Real-time updates to connected clients via WebSocket (Laravel Reverb)
- Location: `app/Events/`
- Contains: `ActionCompleted`, `CheckInReminder`, `FriendRequestReceived`, `NewCommunityPost`, `VisionUpdated`
- Depends on: Models
- Used by: Controllers (via `event()` helper)

**Admin Panel Layer:**
- Purpose: Filament v5 CRUD interface for content management
- Location: `app/Filament/`
- Contains: Resources for Action, CommunityPost, IslamicEvent, JournalEntry, Quiz, Reel, Trait, User
- Depends on: Models
- Used by: Filament panel at `/admin`

## Data Flow

**Authenticated API Request:**

1. Request arrives at `public/index.php`
2. Laravel HTTP kernel applies global middleware (including `SetLocale`)
3. Route matched in `routes/api.php`; `auth:sanctum` middleware validates Bearer token
4. Controller method invoked; `$request->validate()` handles input validation
5. Controller queries models via Eloquent (always scoped to `$request->user()`)
6. Controller returns `JsonResource` or `response()->json()` with Eloquent data
7. Optional: `event()` dispatched — broadcasts via Reverb to private channel

**Google OAuth (Mobile Token Flow):**

1. Mobile app obtains Google ID token natively
2. POST `/api/auth/google/token` with `id_token`
3. `AuthController::googleToken()` verifies token with Google's tokeninfo API
4. User found-or-created by `google_id`, then by `email`
5. Sanctum personal access token created and returned

**Push Notification Flow:**

1. User subscribes via POST `/api/push/subscribe` → stored in `push_subscriptions`
2. Controller or admin triggers `PushNotificationService::sendToUser()` or `sendToAll()`
3. VAPID-signed Web Push payload queued and flushed; expired subscriptions auto-deleted

**State Management:**
- No client-side state managed server-side; all state is in database
- Timeline events auto-written on action creation/check-in to create an audit trail in `timeline_events`

## Key Abstractions

**HasUuid Trait:**
- Purpose: Provides UUID primary keys to all models
- Examples: Used by every model in `app/Models/`
- Pattern: Boots on `creating` event; sets `incrementing=false` and `keyType='string'`
- File: `app/Traits/HasUuid.php`

**Trait_ Model (Naming Exception):**
- Purpose: Character traits library with localization support
- Examples: `app/Models/Trait_.php`
- Pattern: Named `Trait_` (underscore suffix) because `Trait` is a PHP reserved word; maps to `traits` table
- Localization: `getLocalized*Attribute()` accessors return `*_ms` column values when locale is `ms`

**API Resources:**
- Purpose: Decouple model shape from API response shape; apply localization in responses
- Examples: `app/Http/Resources/TraitResource.php` maps `localized_name` from model accessor
- Pattern: Most resources use `parent::toArray()` (pass-through); `TraitResource` customizes for i18n

**TimelineEvent Auto-logging:**
- Purpose: Audit trail of user activity (actions created, check-ins completed)
- Pattern: Controllers directly create `TimelineEvent` records inline after domain operations
- File: `app/Models/TimelineEvent.php`

## Entry Points

**API:**
- Location: `public/index.php` → `bootstrap/app.php`
- Triggers: HTTP requests to `/api/*`
- Responsibilities: All mobile client endpoints

**Admin Panel:**
- Location: `app/Providers/Filament/AdminPanelProvider.php`
- Triggers: HTTP requests to `/admin`
- Responsibilities: Filament CRUD panel; auto-discovers resources from `app/Filament/Resources/`

**Artisan / Console:**
- Location: `routes/console.php`
- Triggers: CLI commands

**WebSocket Channels:**
- Location: `routes/channels.php`
- Triggers: Channel authorization requests from Reverb clients
- Channels: `user.{id}` (private), `friends.{id}` (friends-only), `community` (all auth users)

## Error Handling

**Strategy:** Laravel defaults — validation errors return 422 with `errors` object; authentication failures return 401/403; `findOrFail()` returns 404.

**Patterns:**
- Input validation via `$request->validate()` in every controller method — Laravel auto-returns 422
- `findOrFail()` used throughout to return 404 for missing resources scoped to authenticated user
- Google OAuth wraps Socialite call in try/catch returning 401 on failure
- No global exception handler customization in `bootstrap/app.php`

## Cross-Cutting Concerns

**Logging:** Laravel default (Pail used in dev via `composer dev`)

**Validation:** Inline in controllers via `$request->validate()`; no Form Request classes used

**Authentication:** Laravel Sanctum token-based; Bearer token in `Authorization` header; stateful API enabled for SPA compatibility

**Localization:** `SetLocale` middleware applied to all web and API routes; model accessors used for translated field values

**Rate Limiting:** Defined in `AppServiceProvider::boot()` — `api` (60/min per user/IP), `auth` (5/min per IP)

**Admin Authorization:** `AdminMiddleware` checks `is_admin` boolean or hardcoded `admin@visionaries.pro` email

---

*Architecture analysis: 2026-03-27*
