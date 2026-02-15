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

### UUID Primary Keys
All models use `App\Traits\HasUuid` — a trait that auto-generates UUID primary keys on creation and sets `incrementing=false`, `keyType='string'`. Always use string IDs, never integer auto-increment.

### Domain Model (SEE / BE / DO Framework)
The app is organized around three conceptual pillars:
- **SEE (Vision):** `Vision`, `VisionVersion` — user's life vision statement with version history
- **BE (Identity/Traits):** `Trait_` (table: `traits`), `UserTrait` — character traits library with user selections. Note: the model is named `Trait_` (with underscore) because `Trait` is a PHP reserved word.
- **DO (Actions):** `Action`, `ActionCheckIn` — daily habits/actions with check-in tracking

Supporting models: `JournalEntry`, `Review`, `CheckIn` (spiritual check-ins), `QuizAttempt`, `TimelineEvent`, `CommunityPost`, `CommunityComment`, `CommunityLike`, `Reel`, `Quiz`, `IslamicEvent`.

### i18n / Localization
- Supported locales: `en` (English), `ms` (Malay)
- `SetLocale` middleware resolves locale from: authenticated user preference → `?locale=` param → `Accept-Language` header → default `en`
- The `Trait_` model has `*_ms` columns for Malay translations and `getLocalized*Attribute()` accessors
- Translation files in `lang/ms/` and `lang/ms.json`

### API Resources (Response Serialization)
All API responses use Laravel API Resources in `app/Http/Resources/` to transform models into JSON. When adding new endpoints, create a corresponding resource class.

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
