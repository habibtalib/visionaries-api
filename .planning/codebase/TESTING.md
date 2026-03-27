# Testing Patterns

**Analysis Date:** 2026-03-27

## Test Framework

**Runner:**
- PHPUnit 11.5.3
- Config: `phpunit.xml`
- Bootstrap: `vendor/autoload.php`

**Assertion Library:**
- PHPUnit built-in assertions
- Laravel's `TestCase` HTTP assertion helpers (`assertStatus`, `assertJson`, etc.)

**Run Commands:**
```bash
composer test                          # Clears config cache then runs all tests
php artisan test                       # Run all tests directly
php artisan test --filter=ClassName    # Run specific test class
php artisan test --filter=method_name  # Run specific test method
php artisan test tests/Feature/ExampleTest.php  # Run specific file
```

## Test File Organization

**Location:**
- Feature tests: `tests/Feature/`
- Unit tests: `tests/Unit/`
- Separate from source code (not co-located)

**Naming:**
- Files: PascalCase with `Test` suffix — e.g., `ExampleTest.php`
- Methods: snake_case with `test_` prefix — e.g., `test_the_application_returns_a_successful_response()`

**Structure:**
```
tests/
├── TestCase.php          # Base test case (extends Laravel's TestCase)
├── Feature/
│   └── ExampleTest.php   # HTTP/integration tests
└── Unit/
    └── ExampleTest.php   # Pure unit tests
```

## Test State

**Current Coverage:**
- Only Laravel scaffold example tests exist — `tests/Feature/ExampleTest.php` and `tests/Unit/ExampleTest.php`
- **No application-specific tests have been written**
- All API controllers, models, services, and events are untested

**Base Test Case:**
```php
// tests/TestCase.php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    //
}
```
No traits or setup added to base — `RefreshDatabase` is commented out in `ExampleTest.php`.

## Test Environment Configuration

All configured in `phpunit.xml`:

```xml
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="BCRYPT_ROUNDS" value="4"/>
<env name="BROADCAST_CONNECTION" value="null"/>
<env name="CACHE_STORE" value="array"/>
<env name="MAIL_MAILER" value="array"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="SESSION_DRIVER" value="array"/>
<env name="PULSE_ENABLED" value="false"/>
<env name="TELESCOPE_ENABLED" value="false"/>
```

**Key implications:**
- Database: SQLite in-memory — no external DB needed for tests
- Queue: sync — jobs execute immediately in tests
- Broadcasts: null driver — no WebSocket connections in tests
- Cache/Session: array — ephemeral, no Redis needed

## Available Testing Infrastructure

**Mocking Package:**
- `mockery/mockery` ^1.6 is installed but unused in any tests currently

**Factory:**
- `database/factories/UserFactory.php` exists (Laravel scaffold default)
- Factory uses `fake()->name()`, `fake()->unique()->safeEmail()` — generates generic user data
- **Note:** `UserFactory` does not reflect the actual `User` model's `$fillable` fields — it uses `name` but the model uses `display_name`. The factory would fail if used as-is.
- No other model factories exist beyond the scaffold `UserFactory`

**Seeders for Development:**
- `TraitSeeder` — seeds 40+ Islamic character traits
- `AdminUserSeeder` — creates `admin@visionaries.pro`
- `TestUserSeeder` — creates `test@visionaries.pro` with full data set (vision, actions, check-ins, journal, traits, timeline, reviews)

## Recommended Test Patterns (Based on Codebase Structure)

When writing tests, follow these patterns consistent with the codebase architecture:

**Feature Test Structure:**
```php
namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ActionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_create_action(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/actions', [
                'title' => 'Morning dhikr',
                'domain' => 'spiritual',
                'frequency' => 'daily',
                'alignment' => 'Connection with Allah',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('action.title', 'Morning dhikr');
    }
}
```

**Authentication in Tests:**
- Use `$this->actingAs($user, 'sanctum')` for protected routes
- All `/api/` routes except register/login/push/vapid-key require `auth:sanctum`

**UUID Awareness:**
- All model IDs are UUIDs (strings), not integers
- Use model IDs directly: `$action->id` not `1`
- Route parameters are `string $id` — pass UUID strings in test URLs

**Database Refresh:**
- Use `RefreshDatabase` trait on feature tests that touch the database
- SQLite in-memory resets between test runs automatically

**Validation Testing:**
```php
public function test_store_requires_title(): void
{
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/actions', ['domain' => 'spiritual']);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'frequency', 'alignment']);
}
```

**Soft Delete Awareness:**
- `Action`, `JournalEntry`, `CommunityPost`, `User` use `SoftDeletes`
- Deleted records won't appear in queries by default; use `withTrashed()` to assert soft deletion

## Mocking

**Framework:** Mockery (installed, unused currently)

**Laravel Built-in Faking:**
```php
// Fake events to prevent broadcast
Event::fake();
Event::assertDispatched(ActionCompleted::class);

// Fake HTTP calls (for Google token verification in tests)
Http::fake([
    'oauth2.googleapis.com/*' => Http::response(['sub' => '123', 'email' => 'test@example.com', 'aud' => 'client-id'], 200),
]);

// Fake notifications
Notification::fake();
Notification::assertSentTo($user, FriendRequestNotification::class);
```

**What to Mock:**
- `OneSignalService` HTTP calls to `onesignal.com`
- `PushNotificationService` WebPush operations
- Google OAuth HTTP calls in `AuthController::googleToken()`
- Broadcasts/Events when testing controller logic

**What NOT to Mock:**
- Eloquent model operations (use `RefreshDatabase` with SQLite instead)
- Rate limiting in most tests (test env doesn't enforce it)
- `TimelineEvent::create()` calls (these are lightweight DB writes, fine in tests)

## Fixtures and Factories

**Test Data:**
- Only `UserFactory` exists currently (scaffold default, mismatched with actual model)
- `TestUserSeeder` provides a comprehensive data fixture for manual/development testing, not automated tests

**Creating Test Users Manually:**
```php
// Minimal user creation that works with actual model fields
$user = User::create([
    'email' => 'test@example.com',
    'display_name' => 'Test User',
    'password' => 'password',
    'auth_provider' => 'email',
]);
```

**Recommended factory approach for new tests:**
- Create a properly-mapped `UserFactory` definition that uses `display_name` not `name`
- Add factories for `Action`, `JournalEntry`, `CheckIn` as tests are written

## Coverage

**Requirements:** None enforced — no coverage thresholds in `phpunit.xml`

**Coverage Source:**
- `phpunit.xml` includes source for coverage: `<directory>app</directory>`

**View Coverage:**
```bash
php artisan test --coverage
```

## Test Types

**Unit Tests (`tests/Unit/`):**
- For pure PHP logic without framework dependencies
- Extend `PHPUnit\Framework\TestCase` directly (not Laravel's)
- Intended for: model accessors, service calculation logic, helper functions

**Feature Tests (`tests/Feature/`):**
- For HTTP/API endpoint testing with full Laravel framework boot
- Extend `Tests\TestCase` (which extends Laravel's `TestCase`)
- Intended for: full request/response cycles, auth flows, validation

**E2E Tests:**
- Not configured — no browser automation framework (Dusk, Playwright, etc.)

## Current Test Gap Summary

The following areas have zero test coverage:

- All 17 API controllers in `app/Http/Controllers/Api/`
- All 5 events in `app/Events/`
- All 3 notifications in `app/Notifications/`
- Both services: `PushNotificationService`, `OneSignalService`
- All 3 console commands in `app/Console/Commands/`
- `HasUuid` trait behavior
- All Filament admin resources

**Priority areas to test first:**
- `AuthController` — register, login, Google token flow
- `ActionController` — CRUD + check-in
- `VisionController` — create, update, history versioning
- `CheckInController` — upsert logic
- `CommunityController` — feed, like toggle, comments

---

*Testing analysis: 2026-03-27*
