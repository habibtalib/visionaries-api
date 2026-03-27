# Codebase Structure

**Analysis Date:** 2026-03-27

## Directory Layout

```
visionaries-api/
├── app/
│   ├── Console/Commands/        # Artisan commands
│   ├── Events/                  # Broadcastable events (Reverb/WebSocket)
│   ├── Filament/
│   │   ├── Resources/           # Filament admin CRUD resources
│   │   └── Widgets/             # Filament dashboard widgets
│   ├── Http/
│   │   ├── Controllers/Api/     # API controllers (one per domain area)
│   │   ├── Middleware/          # HTTP middleware (SetLocale, AdminMiddleware)
│   │   └── Resources/          # Laravel API Resource classes
│   ├── Models/                  # Eloquent models
│   ├── Notifications/           # Laravel notification classes
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   └── Filament/AdminPanelProvider.php
│   ├── Services/                # External service wrappers
│   └── Traits/                  # Shared model traits
├── bootstrap/
│   └── app.php                  # Application bootstrap (routing, middleware config)
├── config/                      # Laravel config files
├── database/
│   ├── factories/               # Model factories for testing
│   ├── migrations/              # Ordered date-prefixed migration files
│   └── seeders/                 # Database seeders
├── lang/
│   └── ms/                      # Malay translation files
├── public/                      # Web root (index.php, compiled assets)
├── resources/
│   ├── css/                     # Source CSS
│   ├── js/                      # Source JS
│   └── views/                   # Blade templates (minimal — API-first)
├── routes/
│   ├── api.php                  # All API routes
│   ├── channels.php             # WebSocket broadcast channel authorization
│   ├── console.php              # Artisan scheduled commands
│   └── web.php                  # Web routes (admin redirect, Filament)
├── storage/                     # Logs, cache, sessions (gitignored contents)
├── tests/
│   ├── Feature/                 # Feature/integration tests
│   └── Unit/                    # Unit tests
├── .planning/codebase/          # GSD codebase analysis documents
├── CLAUDE.md                    # Project guidance for Claude Code
├── composer.json                # PHP dependencies and scripts
├── package.json                 # JS dependencies
├── phpunit.xml                  # Test configuration (SQLite in-memory)
└── vite.config.js               # Frontend build config
```

## Directory Purposes

**`app/Http/Controllers/Api/`:**
- Purpose: All REST API controllers; one file per domain area
- Contains: 19 controller files
- Key files:
  - `app/Http/Controllers/Api/AuthController.php` — registration, login, Google OAuth
  - `app/Http/Controllers/Api/ActionController.php` — DO pillar; actions and check-ins
  - `app/Http/Controllers/Api/VisionController.php` — SEE pillar; vision CRUD
  - `app/Http/Controllers/Api/TraitController.php` — BE pillar; trait library and user traits
  - `app/Http/Controllers/Api/DashboardController.php` — today's summary aggregation
  - `app/Http/Controllers/Api/AdminPushController.php` — admin broadcast push notifications

**`app/Http/Resources/`:**
- Purpose: Transform Eloquent models into JSON API responses
- Contains: 13 resource classes, all extending `JsonResource`
- Key files:
  - `app/Http/Resources/TraitResource.php` — custom toArray with localized field accessors
  - `app/Http/Resources/AiSuggestionResource.php` — suggestion response shape

**`app/Models/`:**
- Purpose: Eloquent ORM models; all use `HasUuid` trait
- Contains: 18 model files
- Key files:
  - `app/Models/User.php` — central model; all domain relationships hang off User
  - `app/Models/Trait_.php` — named with underscore due to PHP reserved word; has `_ms` localization columns
  - `app/Models/Action.php`, `app/Models/ActionCheckIn.php` — DO pillar
  - `app/Models/Vision.php`, `app/Models/VisionVersion.php` — SEE pillar
  - `app/Models/TimelineEvent.php` — audit log of user activity

**`app/Services/`:**
- Purpose: Thin wrappers around external SDKs
- Key files:
  - `app/Services/PushNotificationService.php` — VAPID Web Push via `minishlink/web-push`
  - `app/Services/OneSignalService.php` — OneSignal push delivery

**`app/Events/`:**
- Purpose: Broadcastable events dispatched from controllers; sent via Laravel Reverb
- Key files:
  - `app/Events/ActionCompleted.php` — broadcasts to `friends.{id}` private channel
  - `app/Events/VisionUpdated.php`, `app/Events/NewCommunityPost.php`, `app/Events/CheckInReminder.php`, `app/Events/FriendRequestReceived.php`

**`app/Http/Middleware/`:**
- Purpose: Request pipeline interceptors
- Key files:
  - `app/Http/Middleware/SetLocale.php` — locale resolution from user preference, query param, or Accept-Language header
  - `app/Http/Middleware/AdminMiddleware.php` — admin route guard

**`app/Traits/`:**
- Purpose: Reusable model behaviors
- Key files:
  - `app/Traits/HasUuid.php` — auto-generates UUID on `creating`; sets `incrementing=false`, `keyType='string'`

**`app/Filament/Resources/`:**
- Purpose: Filament v5 admin panel resource definitions; auto-discovered
- Contains: ActionResource, CommunityPostResource, IslamicEventResource, JournalEntryResource, QuizResource, ReelResource, TraitResource, UserResource (each with a `Pages/` subdirectory)

**`database/migrations/`:**
- Purpose: Ordered schema definitions
- Naming: `YYYY_MM_DD_NNNNNN_create_{table}_table.php`
- Pattern: Domain tables start at `2024_01_01_000010_*`; feature additions are date-stamped when added

**`database/seeders/`:**
- Purpose: Seed test and reference data
- Key files:
  - `database/seeders/TraitSeeder.php` — seeds character traits library
  - `database/seeders/AdminUserSeeder.php` — creates admin user
  - `database/seeders/TestUserSeeder.php` — creates test user for development
  - `database/seeders/DatabaseSeeder.php` — orchestrates all seeders

**`lang/ms/`:**
- Purpose: Malay translation strings
- Contains: Translation files for locale `ms`

## Key File Locations

**Entry Points:**
- `public/index.php`: Web entry point
- `bootstrap/app.php`: Application bootstrap (routing, middleware registration)
- `routes/api.php`: All API route definitions
- `routes/channels.php`: WebSocket channel authorization

**Configuration:**
- `bootstrap/app.php`: Global middleware, route files
- `app/Providers/AppServiceProvider.php`: Rate limiting configuration
- `app/Providers/Filament/AdminPanelProvider.php`: Admin panel setup, colors, auto-discovery paths
- `phpunit.xml`: Test environment config (SQLite in-memory)

**Core Logic:**
- `app/Http/Controllers/Api/`: All business logic lives here (no separate service/domain layer except push)
- `app/Models/Trait_.php`: Localization accessors pattern (reference for i18n)
- `app/Traits/HasUuid.php`: UUID pattern applied to all models

**Testing:**
- `tests/Feature/ExampleTest.php`: Feature test baseline
- `tests/Unit/ExampleTest.php`: Unit test baseline

## Naming Conventions

**Files:**
- Models: `PascalCase.php` (exception: `Trait_.php` with underscore suffix)
- Controllers: `{Domain}Controller.php` (e.g., `ActionController.php`)
- Resources: `{Model}Resource.php` (e.g., `TraitResource.php`)
- Events: `{PastTenseAction}.php` (e.g., `ActionCompleted.php`)
- Migrations: `YYYY_MM_DD_NNNNNN_{verb}_{table}_table.php`

**Directories:**
- Lowercase for `routes/`, `database/`, `lang/`, `config/`, `resources/`
- PascalCase for `app/Http/Controllers/Api/`, `app/Filament/Resources/`

**Models:**
- Class names: PascalCase (e.g., `CommunityPost`, `ActionCheckIn`)
- Table names: snake_case plural (e.g., `community_posts`, `action_check_ins`)
- Exception: `Trait_` model maps to `traits` table

## Where to Add New Code

**New API Endpoint:**
- Controller: `app/Http/Controllers/Api/{Domain}Controller.php`
- Resource: `app/Http/Resources/{Model}Resource.php`
- Route: `routes/api.php` inside the `auth:sanctum` middleware group
- Model (if new): `app/Models/{Model}.php` — must use `HasUuid` trait
- Migration: `database/migrations/YYYY_MM_DD_NNNNNN_create_{table}_table.php`

**New Admin Panel Feature:**
- Resource: `app/Filament/Resources/{Model}Resource/` with a `Pages/` subdirectory
- Auto-discovered — no manual registration needed

**New Broadcast Event:**
- Event class: `app/Events/{EventName}.php` implementing `ShouldBroadcast`
- Channel authorization: `routes/channels.php` if using a new channel name

**New External Service Integration:**
- Service wrapper: `app/Services/{ServiceName}Service.php`

**New Middleware:**
- File: `app/Http/Middleware/{Name}Middleware.php`
- Register alias in `bootstrap/app.php` `withMiddleware()` block

**New Model Trait:**
- File: `app/Traits/{TraitName}.php`

**New Seeder:**
- File: `database/seeders/{Name}Seeder.php`
- Register in `database/seeders/DatabaseSeeder.php`

## Special Directories

**`.planning/`:**
- Purpose: GSD project planning documents and codebase analysis
- Generated: No (manually maintained)
- Committed: Yes

**`bootstrap/cache/`:**
- Purpose: Laravel compiled bootstrap cache (config, routes, packages)
- Generated: Yes (via `php artisan config:cache`, `route:cache`)
- Committed: No

**`storage/`:**
- Purpose: Logs, framework cache, sessions, compiled views
- Generated: Yes
- Committed: No (except directory structure)

**`public/js/filament/` and `public/css/filament/`:**
- Purpose: Compiled Filament frontend assets
- Generated: Yes (via `composer setup` / `php artisan filament:assets`)
- Committed: Yes (pre-built)

**`vendor/`:**
- Purpose: Composer PHP dependencies
- Generated: Yes
- Committed: No

---

*Structure analysis: 2026-03-27*
