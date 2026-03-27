# External Integrations

**Analysis Date:** 2026-03-27

## APIs & External Services

**Push Notifications (OneSignal):**
- OneSignal - Broadcast push notifications to all mobile app users
  - SDK/Client: Laravel HTTP facade (`Illuminate\Support\Facades\Http`) calling `https://onesignal.com/api/v1/notifications`
  - Service: `app/Services/OneSignalService.php`
  - Auth env vars: `ONESIGNAL_APP_ID`, `ONESIGNAL_REST_API_KEY`
  - Used by: `app/Http/Controllers/Api/AdminPushController.php` (admin broadcast endpoint)
  - Endpoints used: `POST /api/v1/notifications`, `GET /api/v1/apps/{appId}`
  - Note: Falls back gracefully if not configured — `isConfigured()` check before every call

**Push Notifications (Web Push / VAPID):**
- Browser Web Push API (standards-based) - Per-user push to subscribed browsers/PWA
  - SDK/Client: `minishlink/web-push` v10.0.1
  - Service: `app/Services/PushNotificationService.php`
  - Auth env vars: `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT`
  - Subscriptions stored in: `push_subscriptions` table (model: `app/Models/PushSubscription.php`)
  - Public key endpoint: `GET /api/push/vapid-key` (unauthenticated)
  - Subscribe/unsubscribe: `POST /api/push/subscribe`, `DELETE /api/push/unsubscribe` (authenticated)
  - Expired subscriptions are auto-deleted on failed send

**Google OAuth:**
- Google OAuth 2.0 via Laravel Socialite v5.24.2
  - SDK/Client: `Laravel\Socialite\Facades\Socialite`
  - Implementation: `app/Http/Controllers/Api/AuthController.php`
  - Auth env vars: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`
  - Configured in: `config/services.php` under `google` key
  - Routes:
    - `GET /api/auth/google/redirect` - Returns OAuth redirect URL as JSON
    - `GET /api/auth/google/callback` - Handles web OAuth callback
    - `POST /api/auth/google/token` - Token exchange for mobile (accepts Google ID token)
  - Flow: Stateless OAuth; creates or updates user with `auth_provider = 'google'` and stores `google_id`

## Data Storage

**Databases:**
- SQLite (default/development)
  - Connection: `DB_CONNECTION=sqlite`, `DB_DATABASE` defaults to `database/database.sqlite`
  - Client: Laravel Eloquent ORM
- MySQL / MariaDB (production option)
  - Connection: `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
  - Configured in `config/database.php` but not the default

**File Storage:**
- Local filesystem only (default: `FILESYSTEM_DISK=local`)
- AWS S3 configured as an option (`AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_BUCKET`) but not confirmed in active use

**Caching:**
- Database cache store by default (`CACHE_STORE=database`)
- Redis available as an option (`REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`)
- Memcached available as an option (`MEMCACHED_HOST`)

**Session:**
- Database-backed sessions by default (`SESSION_DRIVER=database`)

**Queue:**
- Database queue by default (`QUEUE_CONNECTION=database`)
- Worker started via `composer dev` as `php artisan queue:listen --tries=1 --timeout=0`

## Authentication & Identity

**API Authentication:**
- Laravel Sanctum v4.3.1 (token-based)
  - Implementation: `auth:sanctum` middleware on all protected routes
  - Stateful API enabled in `bootstrap/app.php` via `$middleware->statefulApi()`
  - Personal access tokens stored in `personal_access_tokens` table
  - Token issued on register/login/google-auth; deleted on logout

**Admin Authentication:**
- Filament v5 panel at `/admin` with own session-based login
- API admin routes protected by custom `AdminMiddleware` (`app/Http/Middleware/AdminMiddleware.php`)
- Admin check: `$user->is_admin === true` OR `$user->email === 'admin@visionaries.pro'`

## Real-time Broadcasting (WebSockets)

**WebSocket Server:**
- Laravel Reverb v1.7.1 — self-hosted WebSocket server
  - Server env vars: `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_APP_ID`, `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME`
  - Scales via Redis (optional): `REVERB_SCALING_ENABLED`, uses `REDIS_HOST`/`REDIS_PORT`
  - Configured in `config/reverb.php`

**Broadcast Channels** (defined in `routes/channels.php`):
- `user.{id}` — Private; user can only subscribe to own channel
- `friends.{id}` — Private; requires accepted friendship in `friends` table
- `community` — Authenticated users only

**Broadcast Events** (in `app/Events/`):
- `ActionCompleted`
- `CheckInReminder`
- `FriendRequestReceived`
- `NewCommunityPost`
- `VisionUpdated`

## Monitoring & Observability

**Error Tracking:**
- None configured (no Sentry, Bugsnag, or Flare packages detected)

**Logging:**
- Laravel's built-in logging via `LOG_CHANNEL` (default: `stack`)
- Log stack configured in `config/logging.php`
- Laravel Pail for real-time log tailing in development (`composer dev`)
- OneSignal errors logged via `Log::error()` in `app/Services/OneSignalService.php`

**Health Check:**
- `GET /up` — Laravel built-in health endpoint (configured in `bootstrap/app.php`)

## Scheduled Jobs

All scheduled via `routes/console.php`, using `Asia/Kuala_Lumpur` timezone:
- `push:daily-morning` — Daily at 08:00 MYT → `app/Console/Commands/PushDailyMorning.php`
- `push:evening-checkin` — Daily at 21:00 MYT → `app/Console/Commands/PushEveningCheckin.php`
- `push:weekly-review` — Every Friday at 09:00 MYT → `app/Console/Commands/PushWeeklyReview.php`

All three commands use `PushNotificationService` (Web Push / VAPID), not OneSignal.

## CI/CD & Deployment

**Hosting:**
- Not explicitly configured (no `Procfile`, no `fly.toml`, no Forge/Vapor config detected)

**CI Pipeline:**
- None detected (no `.github/workflows/`, no `.circleci/`, no `Jenkinsfile`)

## Environment Configuration

**Required env vars (complete list):**
- `APP_KEY` — Laravel app encryption key (generated via `php artisan key:generate`)
- `APP_URL` — Base URL for URL generation
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` — Google OAuth
- `REVERB_APP_KEY`, `REVERB_APP_SECRET`, `REVERB_APP_ID`, `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME` — WebSocket server
- `ONESIGNAL_APP_ID`, `ONESIGNAL_REST_API_KEY` — OneSignal broadcast push (optional; gracefully skipped if absent)
- `VAPID_PUBLIC_KEY`, `VAPID_PRIVATE_KEY`, `VAPID_SUBJECT` — Browser Web Push
- `DB_CONNECTION`, `DB_DATABASE` (+ host/port/user/pass for MySQL) — Database
- `SANCTUM_STATEFUL_DOMAINS` — Comma-separated list of SPA domains for cookie auth

**Secrets location:**
- `.env` file (not committed; `.env.example` committed as template)

## Webhooks & Callbacks

**Incoming:**
- `GET /api/auth/google/callback` — Google OAuth redirect callback (web flow)

**Outgoing:**
- OneSignal REST API: `https://onesignal.com/api/v1/notifications` (admin broadcast)
- Browser Push endpoints stored per-user in `push_subscriptions.endpoint` (varies by browser/user agent)

---

*Integration audit: 2026-03-27*
