# Technology Stack

**Analysis Date:** 2026-03-27

## Languages

**Primary:**
- PHP 8.2+ (runtime: 8.4.19) - All backend logic, API, admin panel
- JavaScript (ES modules) - Frontend assets compiled by Vite

**Secondary:**
- CSS - Tailwind v4 utility classes in `resources/css/app.css`

## Runtime

**Environment:**
- PHP 8.4.19 (built via Laravel Herd)
- Node.js (version managed via project tooling; no `.nvmrc` present)

**Package Manager:**
- PHP: Composer 2.9.5
- JS: npm (lockfile: `package-lock.json` — present via `npm install` in setup script)
- Lockfile: `composer.lock` present and committed

## Frameworks

**Core:**
- Laravel v12.51.0 - Primary application framework (`laravel/framework`)
- Filament v5.2.1 - Admin panel at `/admin` (`filament/filament`)

**Auth:**
- Laravel Sanctum v4.3.1 - Token-based API authentication with stateful API support
- Laravel Socialite v5.24.2 - Google OAuth SSO

**Real-time:**
- Laravel Reverb v1.7.1 - Self-hosted WebSocket server for broadcasting

**Build/Dev:**
- Vite v7.0.7 with `laravel-vite-plugin` v2.0.0 - Asset bundling
- Tailwind CSS v4.0.0 via `@tailwindcss/vite` plugin
- Laravel Pail v1.2.2 - Log viewer for dev
- `concurrently` v9.0.1 - Runs multiple dev processes in parallel (`composer dev`)

**Testing:**
- PHPUnit v11.5.3 - Test runner
- Mockery v1.6 - Mocking library
- Faker v1.23 - Test data generation
- Laravel Sail v1.41 - Docker dev environment (available but not primary)

## Key Dependencies

**Critical:**
- `laravel/sanctum` v4.3.1 - All protected API routes use `auth:sanctum` middleware
- `laravel/socialite` v5.24.2 - Google OAuth flows at `app/Http/Controllers/Api/AuthController.php`
- `laravel/reverb` v1.7.1 - WebSocket broadcasts via channels defined in `routes/channels.php`
- `filament/filament` v5.2.1 - Full admin panel with resources in `app/Filament/Resources/`

**Push Notifications:**
- `minishlink/web-push` v10.0.1 - VAPID-based Web Push API to browser subscribers (`app/Services/PushNotificationService.php`)

**Infrastructure:**
- `laravel/tinker` v2.10.1 - REPL for debugging
- `laravel-lang/common` v6.7 (dev) - i18n translation packages including Malay support
- `nunomaduro/collision` v8.6 (dev) - Better CLI error reporting

## Configuration

**Environment:**
- Configured via `.env` (example at `.env.example`)
- Default database: SQLite at `database/database.sqlite`
- Default queue driver: `database`
- Default cache: `database`
- Default broadcast connection: `log` (set `BROADCAST_CONNECTION=reverb` to enable WebSocket)
- Default mail: `log` mailer

**Required env vars for full functionality:**
- `APP_KEY` - Laravel application encryption key
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` - Google OAuth
- `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_APP_ID`, `REVERB_HOST` - WebSocket server
- `ONESIGNAL_APP_ID`, `ONESIGNAL_REST_API_KEY` - OneSignal push broadcasts
- `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` - Web Push VAPID keys

**Build:**
- `vite.config.js` - Entry points: `resources/css/app.css`, `resources/js/app.js`
- `phpunit.xml` - Test environment: SQLite in-memory, sync queue, array cache
- `config/` directory contains: `app.php`, `auth.php`, `broadcasting.php`, `cache.php`, `database.php`, `filesystems.php`, `logging.php`, `mail.php`, `queue.php`, `reverb.php`, `sanctum.php`, `services.php`, `session.php`

## Platform Requirements

**Development:**
- PHP 8.2+ (tested on 8.4)
- Composer 2.x
- Node.js + npm
- SQLite (default; no external DB required)
- `composer setup` bootstraps full local environment
- `composer dev` runs server + queue + logs + Vite concurrently

**Production:**
- PHP 8.2+ with required extensions (pdo_sqlite or pdo_mysql)
- Queue worker process (`php artisan queue:listen`)
- Reverb server process (`php artisan reverb:start`) if real-time needed
- Scheduler: `php artisan schedule:run` via cron (push notifications fire at 08:00, 21:00 MYT and weekly)
- Deployment target: Not explicitly configured (no Forge/Vapor config detected)

---

*Stack analysis: 2026-03-27*
