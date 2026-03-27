# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Visionaries API is a Laravel 12 backend for an Islamic personal development mobile app. It provides a REST API consumed by a mobile frontend, with a Filament v5 admin panel at `/admin`. The app helps users define a life vision, build character traits, track daily actions, journal, participate in community feeds, and engage with Islamic calendar events.

## Common Commands

- **Setup:** `composer setup` (installs deps, copies .env, generates key, migrates, builds frontend)
- **Dev server:** `composer dev` (runs Laravel server, queue worker, Pail log viewer, and Vite concurrently)
- **Run all tests:** `composer test` (clears config cache, then runs PHPUnit)
- **Run a single test:** `php artisan test --filter=TestClassName` or `php artisan test --filter=test_method_name`
- **Run a test file:** `php artisan test tests/Feature/ExampleTest.php`
- **Lint/format:** `./vendor/bin/pint` (Laravel Pint, PSR-12 style)
- **Migrate:** `php artisan migrate`
- **Seed:** `php artisan db:seed` (runs TraitSeeder, AdminUserSeeder, TestUserSeeder)
- **Seed specific:** `php artisan db:seed --class=TraitSeeder`

## Architecture

### API Authentication
- Laravel Sanctum token-based auth. Stateful API enabled in middleware.
- Google SSO via Laravel Socialite (routes at `/api/auth/google/*`).
- All protected routes use `auth:sanctum` middleware.
- Admin routes additionally use `AdminMiddleware` which checks `is_admin` flag or `email == 'admin@visionaries.pro'`.

### UUID Primary Keys
All models use `App\Traits\HasUuid` — a trait that auto-generates UUID primary keys on creation and sets `incrementing=false`, `keyType='string'`. Always use string IDs, never integer auto-increment.

### Domain Model (SEE / BE / DO Framework)
The app is organized around three conceptual pillars:
- **SEE (Vision):** `Vision`, `VisionVersion` — user's life vision statement with version history
- **BE (Identity/Traits):** `Trait_` (table: `traits`), `UserTrait` — character traits library with user selections. Note: the model is named `Trait_` (with underscore) because `Trait` is a PHP reserved word.
- **DO (Actions):** `Action`, `ActionCheckIn` — daily habits/actions with check-in tracking

Supporting models: `JournalEntry`, `Review`, `CheckIn` (spiritual check-ins), `QuizAttempt`, `TimelineEvent`, `CommunityPost`, `CommunityComment`, `CommunityLike`, `Reel`, `Quiz`, `IslamicEvent`.

### Route Organization
Routes in `routes/api.php` are grouped by domain:
- **Public:** Auth registration/login, VAPID key, Google OAuth
- **Protected** (`auth:sanctum`): Feature endpoints matching the SEE/BE/DO framework
- **Admin** (`auth:sanctum` + `admin`): Push broadcast administration at `/admin/push/*`

### i18n / Localization
- Supported locales: `en` (English), `ms` (Malay)
- `SetLocale` middleware resolves locale from: authenticated user preference → `?locale=` param → `Accept-Language` header → default `en`
- The `Trait_` model has `*_ms` columns for Malay translations and `getLocalized*Attribute()` accessors
- Translation files in `lang/ms/` and `lang/ms.json`

### API Resources (Response Serialization)
All API responses use Laravel API Resources in `app/Http/Resources/` to transform models into JSON. When adding new endpoints, create a corresponding resource class.

### Push Notifications
Two push notification backends:
- **Web Push (VAPID):** `PushNotificationService` using `minishlink/web-push`. Stores `PushSubscription` model per user. Auto-removes expired subscriptions on failed sends.
- **OneSignal:** `OneSignalService` for broadcast push. Optional — gracefully handles unconfigured state.

Scheduled push commands (all in `Asia/Kuala_Lumpur` timezone, defined in `routes/console.php`):
- `push:daily-morning` — daily at 08:00
- `push:evening-checkin` — daily at 21:00
- `push:weekly-review` — Fridays at 09:00

### Notifications
Notifications in `app/Notifications/` use `['database', 'broadcast']` channels. Examples: `ActionReminderNotification`, `FriendRequestNotification`, `WeeklyReviewReminder`.

### Real-time Broadcasting
Laravel Reverb (WebSocket server) with three broadcast channels:
- `user.{id}` — private per-user channel
- `friends.{id}` — private friends channel (requires accepted friendship)
- `community` — authenticated users community channel

Events in `app/Events/` implement `ShouldBroadcast`.

### Filament Admin Panel
Filament v5 at `/admin` with login. Resources auto-discovered from `app/Filament/Resources/`. Panel configured in `app/Providers/Filament/AdminPanelProvider.php` with Amber color theme.

### Database
- Default: SQLite (configured in `.env.example`)
- Tests: SQLite in-memory (configured in `phpunit.xml`)
- Migrations use ordered date prefixes (`2024_01_01_000010_*` for domain tables)

### Rate Limiting
Defined in `AppServiceProvider`:
- `api`: 60 requests/minute per user (or IP if unauthenticated)
- `auth`: 5 requests/minute per IP

<!-- GSD:project-start source:PROJECT.md -->
## Project

**Visionaries API — Backend Parity & Hardening**

The Laravel 12 REST API backend for Visionaries, an Islamic personal development mobile app. The frontend (React + TypeScript) is fully built through v1.1 with 25 pages and 70+ API calls — but several controller routes are missing, some endpoints don't exist yet, and the codebase lacks tests and consistent response formatting. This milestone brings the backend to full parity with the frontend and hardens it for production.

**Core Value:** Every frontend API call hits a working, complete, and well-tested backend endpoint — zero 404s, zero mock data, zero broken features.

### Constraints

- **Tech stack**: Laravel 12 + Sanctum + Reverb — no framework changes
- **Database**: SQLite (development), SQLite in-memory (tests) — migrations must be compatible
- **API contract**: Must match what the React frontend already calls — endpoint paths, request/response shapes are fixed by the frontend code
- **UUID primary keys**: All models use HasUuid trait — string IDs everywhere
- **i18n**: Responses must support en/ms localization where applicable (traits, events)
- **No breaking changes**: Existing working endpoints must not change behavior
<!-- GSD:project-end -->

<!-- GSD:stack-start source:codebase/STACK.md -->
## Technology Stack

## Languages
- PHP 8.2+ (runtime: 8.4.19) - All backend logic, API, admin panel
- JavaScript (ES modules) - Frontend assets compiled by Vite
- CSS - Tailwind v4 utility classes in `resources/css/app.css`
## Runtime
- PHP 8.4.19 (built via Laravel Herd)
- Node.js (version managed via project tooling; no `.nvmrc` present)
- PHP: Composer 2.9.5
- JS: npm (lockfile: `package-lock.json` — present via `npm install` in setup script)
- Lockfile: `composer.lock` present and committed
## Frameworks
- Laravel v12.51.0 - Primary application framework (`laravel/framework`)
- Filament v5.2.1 - Admin panel at `/admin` (`filament/filament`)
- Laravel Sanctum v4.3.1 - Token-based API authentication with stateful API support
- Laravel Socialite v5.24.2 - Google OAuth SSO
- Laravel Reverb v1.7.1 - Self-hosted WebSocket server for broadcasting
- Vite v7.0.7 with `laravel-vite-plugin` v2.0.0 - Asset bundling
- Tailwind CSS v4.0.0 via `@tailwindcss/vite` plugin
- Laravel Pail v1.2.2 - Log viewer for dev
- `concurrently` v9.0.1 - Runs multiple dev processes in parallel (`composer dev`)
- PHPUnit v11.5.3 - Test runner
- Mockery v1.6 - Mocking library
- Faker v1.23 - Test data generation
- Laravel Sail v1.41 - Docker dev environment (available but not primary)
## Key Dependencies
- `laravel/sanctum` v4.3.1 - All protected API routes use `auth:sanctum` middleware
- `laravel/socialite` v5.24.2 - Google OAuth flows at `app/Http/Controllers/Api/AuthController.php`
- `laravel/reverb` v1.7.1 - WebSocket broadcasts via channels defined in `routes/channels.php`
- `filament/filament` v5.2.1 - Full admin panel with resources in `app/Filament/Resources/`
- `minishlink/web-push` v10.0.1 - VAPID-based Web Push API to browser subscribers (`app/Services/PushNotificationService.php`)
- `laravel/tinker` v2.10.1 - REPL for debugging
- `laravel-lang/common` v6.7 (dev) - i18n translation packages including Malay support
- `nunomaduro/collision` v8.6 (dev) - Better CLI error reporting
## Configuration
- Configured via `.env` (example at `.env.example`)
- Default database: SQLite at `database/database.sqlite`
- Default queue driver: `database`
- Default cache: `database`
- Default broadcast connection: `log` (set `BROADCAST_CONNECTION=reverb` to enable WebSocket)
- Default mail: `log` mailer
- `APP_KEY` - Laravel application encryption key
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` - Google OAuth
- `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_APP_ID`, `REVERB_HOST` - WebSocket server
- `ONESIGNAL_APP_ID`, `ONESIGNAL_REST_API_KEY` - OneSignal push broadcasts
- `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` - Web Push VAPID keys
- `vite.config.js` - Entry points: `resources/css/app.css`, `resources/js/app.js`
- `phpunit.xml` - Test environment: SQLite in-memory, sync queue, array cache
- `config/` directory contains: `app.php`, `auth.php`, `broadcasting.php`, `cache.php`, `database.php`, `filesystems.php`, `logging.php`, `mail.php`, `queue.php`, `reverb.php`, `sanctum.php`, `services.php`, `session.php`
## Platform Requirements
- PHP 8.2+ (tested on 8.4)
- Composer 2.x
- Node.js + npm
- SQLite (default; no external DB required)
- `composer setup` bootstraps full local environment
- `composer dev` runs server + queue + logs + Vite concurrently
- PHP 8.2+ with required extensions (pdo_sqlite or pdo_mysql)
- Queue worker process (`php artisan queue:listen`)
- Reverb server process (`php artisan reverb:start`) if real-time needed
- Scheduler: `php artisan schedule:run` via cron (push notifications fire at 08:00, 21:00 MYT and weekly)
- Deployment target: Not explicitly configured (no Forge/Vapor config detected)
<!-- GSD:stack-end -->

<!-- GSD:conventions-start source:CONVENTIONS.md -->
## Conventions

## Naming Patterns
- Controllers: PascalCase, suffix `Controller` — e.g., `ActionController.php`, `VisionController.php`
- Models: PascalCase, singular — e.g., `Action.php`, `JournalEntry.php`
- Exception: PHP reserved word collision handled by trailing underscore — `Trait_.php` (table: `traits`)
- Resources: PascalCase, suffix `Resource` — e.g., `TraitResource.php`, `CommunityPostResource.php`
- Events: PascalCase, noun-verb or event-name form — e.g., `ActionCompleted.php`, `VisionUpdated.php`
- Notifications: PascalCase, suffix `Notification` — e.g., `FriendRequestNotification.php`
- Services: PascalCase, suffix `Service` — e.g., `OneSignalService.php`, `PushNotificationService.php`
- Traits (PHP): PascalCase, in `app/Traits/` — e.g., `HasUuid.php`
- Commands: PascalCase, named after intent — e.g., `PushDailyMorning.php`, `PushWeeklyReview.php`
- Migrations: snake_case with date prefix — e.g., `2024_01_01_000010_create_visions_table.php`
- Seeders: PascalCase, suffix `Seeder` — e.g., `TraitSeeder.php`, `TestUserSeeder.php`
- camelCase for all methods — `store()`, `checkIn()`, `findOrCreateGoogleUser()`
- Private helpers prefixed with visibility keyword — `private function findOrCreateGoogleUser()`
- Eloquent relationship methods use camelCase matching relation type — `hasMany`, `belongsTo`
- camelCase — `$visionId`, `$googleUser`, `$checkIn`
- Snake_case in arrays passed to DB operations — `['user_id' => $userId]`
- PHP 8.2+ typed properties and return types used in new code
- `string` type hints for UUID route parameters — `public function update(Request $request, string $id)`
- Return type hints on service methods — `public function sendToUser(...): array`
- Table names: snake_case plural — `action_check_ins`, `vision_versions`, `community_posts`
- Column names: snake_case — `user_id`, `check_in_date`, `display_name`
- UUID primary keys on all tables via `$table->uuid('id')->primary()`
- Foreign keys use `foreignUuid()` — `$table->foreignUuid('user_id')->constrained()->cascadeOnDelete()`
## Code Style
- Laravel Pint (PSR-12): `./vendor/bin/pint`
- No explicit `.pint.json` config found — uses Pint defaults (Laravel preset)
- No separate ESLint config detected; Pint handles PHP formatting
- Controllers are compact: single-line inline-ifs are common for query filtering
- Models are kept minimal — all on one or few lines when simple:
- More complex models (e.g., `Trait_.php`) use expanded multi-line style for readability
## Import Organization
- No blank line between `<?php` and `namespace` in most files — compact header style
- Imports grouped loosely: framework classes, then app classes (no enforced blank-line separation between groups)
- Example from `ActionController.php`:
- PSR-4 autoloading only — no custom path aliases
- `App\` → `app/`, `Database\Factories\` → `database/factories/`, `Tests\` → `tests/`
## Error Handling
- Validation errors: use `$request->validate([...])` which throws `ValidationException` automatically — Laravel handles 422 response
- Manual validation error: `throw ValidationException::withMessages(['field' => ['message']])` (used in `AuthController`)
- Not-found errors: use `->findOrFail($id)` on relationship queries — Laravel returns 404 automatically
- External API errors: wrap in `try/catch (\Exception $e)` and return JSON error response manually:
- Service errors: catch `\Exception`, log with `Log::error(...)`, return structured error array:
- No global exception handler customization detected beyond Laravel defaults
- `201` for resource creation: `return response()->json($resource, 201);`
- `200` (default) for reads and updates
- `401` for authentication failures
- `404` via `findOrFail()` (automatic)
- `422` via `$request->validate()` (automatic)
## Logging
- Used only for service-level errors — `Log::error("OneSignal error: " . $e->getMessage())`
- Controllers do not log directly
- No structured log context arrays observed — plain string messages only
## Comments
- PHPDoc blocks used for public service methods and non-obvious private helpers:
- Inline comments used for business logic intent, not obvious code:
- Many methods (especially simple CRUD) have no comments
- Not applicable (PHP-only backend)
## Function Design
- Controller methods are generally short (10-30 lines)
- Longer methods acceptable when handling multiple related operations (e.g., `checkIn()` in `ActionController` is ~30 lines)
- Private helpers extracted for reused logic — e.g., `findOrCreateGoogleUser()` in `AuthController`
- Controller methods always receive `Request $request` as first parameter
- Route model binding not used — IDs are `string $id` parameters resolved manually via `findOrFail()`
- Controllers always return `response()->json(...)` or an API Resource
- Service methods return structured arrays: `["success" => bool, ...]`
- Boolean toggle endpoints return `['liked' => true/false]`
- Delete endpoints return `['message' => 'Deleted']`
## Module Design
- All classes use namespaced autoloading — no explicit exports
- Controllers in `App\Http\Controllers\Api\` namespace
- Not used — Laravel routes file imports individual controller classes
## Model Conventions
- All models use `App\Traits\HasUuid` for UUID primary keys
- User-owned models use `SoftDeletes` when deletable via API (e.g., `Action`, `JournalEntry`, `CommunityPost`)
- Models without soft deletes: `Trait_`, `ActionCheckIn`, `CheckIn`, `UserTrait`
- Explicit `$fillable` array required on all models — no mass-assignment `$guarded = []`
- Defined via `protected function casts(): array` (PHP 8 method syntax, not property)
- Booleans always cast explicitly: `'is_active' => 'boolean'`
- DateTime fields cast to `'datetime'` or specific format: `'scheduled_time' => 'datetime:H:i'`
- Methods return Eloquent relation objects, no type hints on relationship methods
- Explicit foreign key only when non-conventional: `$this->hasMany(UserTrait::class, 'trait_id')`
## Resource Conventions
- Every model exposed via API has a corresponding Resource in `app/Http/Resources/`
- Most resources use `parent::toArray($request)` (passthrough) or explicit field mapping
- `TraitResource` shows explicit mapping pattern with localization:
- Several resources (e.g., `ActionResource`, `CommunityPostResource`) are stubs using `parent::toArray()`
## Localization
- Models with translated content have `*_ms` columns alongside English columns
- Localized accessors follow `getLocalized{Field}Attribute()` naming convention
- Resources use `$this->localized_*` to access localized values
- Language strings in `lang/ms/` (array-based) and `lang/ms.json` (key-based)
<!-- GSD:conventions-end -->

<!-- GSD:architecture-start source:ARCHITECTURE.md -->
## Architecture

## Pattern Overview
- Single Laravel application serving both a REST API (consumed by mobile) and a Filament v5 admin panel
- Thin controllers: validation, model interaction, and JSON response — no dedicated service layer for domain logic except push notifications
- All models use UUID primary keys via `App\Traits\HasUuid`
- Domain logic is organized around the SEE/BE/DO framework (Vision, Traits, Actions)
## Layers
- Purpose: Maps HTTP requests to controllers; defines middleware groups
- Location: `routes/api.php`, `routes/web.php`, `routes/channels.php`
- Contains: Route definitions, middleware application, broadcast channel authorization
- Depends on: Controllers, Middleware
- Used by: Laravel HTTP kernel
- Purpose: Cross-cutting concerns applied before controllers execute
- Location: `app/Http/Middleware/`
- Contains:
- Depends on: Auth, Models
- Used by: Route groups in `bootstrap/app.php` and `routes/api.php`
- Purpose: Handles HTTP request/response cycle; validates input; orchestrates model operations
- Location: `app/Http/Controllers/Api/`
- Contains: 19 controllers (one per domain area)
- Depends on: Models, Resources, Events, Services
- Used by: Routes
- Purpose: Eloquent ORM models; relationships; attribute accessors; casts
- Location: `app/Models/`
- Contains: 18 models covering all domain entities
- Depends on: `HasUuid` trait, Eloquent
- Used by: Controllers, Resources, Filament Resources, Seeders
- Purpose: Transforms Eloquent models into JSON API responses
- Location: `app/Http/Resources/`
- Contains: 13 resource classes extending `JsonResource`
- Depends on: Models
- Used by: Controllers (returned directly as response)
- Purpose: Encapsulates external service integrations
- Location: `app/Services/`
- Contains:
- Depends on: Models, external SDKs
- Used by: Controllers
- Purpose: Real-time updates to connected clients via WebSocket (Laravel Reverb)
- Location: `app/Events/`
- Contains: `ActionCompleted`, `CheckInReminder`, `FriendRequestReceived`, `NewCommunityPost`, `VisionUpdated`
- Depends on: Models
- Used by: Controllers (via `event()` helper)
- Purpose: Filament v5 CRUD interface for content management
- Location: `app/Filament/`
- Contains: Resources for Action, CommunityPost, IslamicEvent, JournalEntry, Quiz, Reel, Trait, User
- Depends on: Models
- Used by: Filament panel at `/admin`
## Data Flow
- No client-side state managed server-side; all state is in database
- Timeline events auto-written on action creation/check-in to create an audit trail in `timeline_events`
## Key Abstractions
- Purpose: Provides UUID primary keys to all models
- Examples: Used by every model in `app/Models/`
- Pattern: Boots on `creating` event; sets `incrementing=false` and `keyType='string'`
- File: `app/Traits/HasUuid.php`
- Purpose: Character traits library with localization support
- Examples: `app/Models/Trait_.php`
- Pattern: Named `Trait_` (underscore suffix) because `Trait` is a PHP reserved word; maps to `traits` table
- Localization: `getLocalized*Attribute()` accessors return `*_ms` column values when locale is `ms`
- Purpose: Decouple model shape from API response shape; apply localization in responses
- Examples: `app/Http/Resources/TraitResource.php` maps `localized_name` from model accessor
- Pattern: Most resources use `parent::toArray()` (pass-through); `TraitResource` customizes for i18n
- Purpose: Audit trail of user activity (actions created, check-ins completed)
- Pattern: Controllers directly create `TimelineEvent` records inline after domain operations
- File: `app/Models/TimelineEvent.php`
## Entry Points
- Location: `public/index.php` → `bootstrap/app.php`
- Triggers: HTTP requests to `/api/*`
- Responsibilities: All mobile client endpoints
- Location: `app/Providers/Filament/AdminPanelProvider.php`
- Triggers: HTTP requests to `/admin`
- Responsibilities: Filament CRUD panel; auto-discovers resources from `app/Filament/Resources/`
- Location: `routes/console.php`
- Triggers: CLI commands
- Location: `routes/channels.php`
- Triggers: Channel authorization requests from Reverb clients
- Channels: `user.{id}` (private), `friends.{id}` (friends-only), `community` (all auth users)
## Error Handling
- Input validation via `$request->validate()` in every controller method — Laravel auto-returns 422
- `findOrFail()` used throughout to return 404 for missing resources scoped to authenticated user
- Google OAuth wraps Socialite call in try/catch returning 401 on failure
- No global exception handler customization in `bootstrap/app.php`
## Cross-Cutting Concerns
<!-- GSD:architecture-end -->

<!-- GSD:workflow-start source:GSD defaults -->
## GSD Workflow Enforcement

Before using Edit, Write, or other file-changing tools, start work through a GSD command so planning artifacts and execution context stay in sync.

Use these entry points:
- `/gsd:quick` for small fixes, doc updates, and ad-hoc tasks
- `/gsd:debug` for investigation and bug fixing
- `/gsd:execute-phase` for planned phase work

Do not make direct repo edits outside a GSD workflow unless the user explicitly asks to bypass it.
<!-- GSD:workflow-end -->

<!-- GSD:profile-start -->
## Developer Profile

> Profile not yet configured. Run `/gsd:profile-user` to generate your developer profile.
> This section is managed by `generate-claude-profile` -- do not edit manually.
<!-- GSD:profile-end -->
