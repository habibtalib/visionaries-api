# Architecture Patterns

**Domain:** Laravel 12 REST API -- backend parity and hardening
**Researched:** 2026-03-27

## Recommended Architecture

The existing architecture is a layered MVC API monolith. The hardening milestone does not change this fundamental pattern -- it completes it. The codebase currently has gaps in every layer: missing routes, missing models, missing migrations, pass-through resources, no Form Requests, no tests, and no AI service integration. The architecture below describes the target state after hardening.

### Component Boundary Diagram

```
HTTP Request
    |
    v
[routes/api.php] ---- Route Groups (auth:sanctum middleware)
    |
    v
[Form Requests] ---- Validation + Authorization (NEW: app/Http/Requests/)
    |
    v
[Controllers] ---- Orchestration only: validate, call service/model, return resource
    |                |
    |                +--> [Services] ---- External integrations (ClaudeService, PushNotificationService)
    |                |
    |                +--> [Models] ---- Eloquent queries, relationships, scopes
    |                |
    |                +--> [Events] ---- Broadcast via Reverb
    |
    v
[API Resources] ---- Response transformation (explicit field mapping, no pass-throughs)
    |
    v
HTTP Response (JSON)
```

### Component Boundaries

| Component | Responsibility | Communicates With | Location |
|-----------|---------------|-------------------|----------|
| Route Definitions | HTTP verb + URI to controller mapping, middleware assignment | Controllers, Middleware | `routes/api.php` |
| Form Requests | Input validation rules, authorization checks | Controllers (injected via type-hint) | `app/Http/Requests/` (NEW) |
| Controllers | Orchestrate request lifecycle: accept validated input, call models/services, return resources | Form Requests, Models, Services, Events, Resources | `app/Http/Controllers/Api/` |
| Models | Data access, relationships, attribute accessors, query scopes | Database, HasUuid trait | `app/Models/` |
| Services | Encapsulate external API calls and complex domain logic | External APIs (Claude, OneSignal), Models | `app/Services/` |
| API Resources | Transform models into JSON response shape | Models (read-only) | `app/Http/Resources/` |
| Events | Broadcast real-time updates via Reverb WebSocket | Models (payload), Broadcasting channels | `app/Events/` |
| Factories | Generate test data for models | Models | `database/factories/` |
| Feature Tests | Exercise full HTTP request/response cycle | Routes, Database (in-memory SQLite) | `tests/Feature/` |

### Data Flow

**Standard authenticated API request (target state):**

1. Request hits `routes/api.php`, matched to controller method
2. `auth:sanctum` middleware validates Bearer token, populates `$request->user()`
3. `SetLocale` middleware resolves locale
4. Form Request class validates input and authorizes action (replaces `$request->validate()`)
5. Controller calls Model queries (scoped to authenticated user) or Service methods
6. Controller optionally dispatches Event for broadcast
7. Controller returns API Resource wrapping the model/collection
8. Resource `toArray()` maps explicit fields (no `parent::toArray()` pass-through)
9. JSON response returned to client

**AI suggestion generation flow (new):**

1. POST `/api/suggestions/generate` hits `SuggestionController::generate()`
2. Controller gathers user context: vision statement, selected traits, existing actions
3. Controller calls `ClaudeService::generateSuggestions($context)`
4. ClaudeService builds prompt, calls Anthropic Messages API, parses response
5. Controller wraps result in `AiSuggestionResource` and returns

**Friend request flow (newly routed):**

1. POST `/api/friends/request` with `{friend_id}` hits `FriendController::sendRequest()`
2. Friend record created with `status: pending`
3. `FriendRequestReceived` event broadcast to target user's private channel
4. `FriendRequestNotification` sent to target user
5. Response returned with success message

## Patterns to Follow

### Pattern 1: Form Request Classes Replace Inline Validation

**What:** Extract `$request->validate([...])` calls from controllers into dedicated Form Request classes. Each controller action that accepts input gets its own Form Request.

**When:** Every controller method that currently calls `$request->validate()`.

**Why:** Centralizes validation rules, enables reuse, makes controllers thinner, and adds `authorize()` method for ownership checks.

**Example:**

```php
// app/Http/Requests/StoreActionRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // auth:sanctum handles authentication
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'domain' => 'required|string|in:spiritual,knowledge,family,health,professional,community',
            'frequency' => 'required|string|in:daily,weekly,monthly,yearly,lifetime',
            'alignment' => 'required|string|min:1',
            'scheduled_time' => 'nullable|date_format:H:i',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ];
    }
}

// In controller: type-hint instead of manual validate
public function store(StoreActionRequest $request)
{
    $data = $request->validated();
    // ...
}
```

**Naming convention:** `{Verb}{Model}Request.php` -- e.g., `StoreActionRequest`, `UpdateActionRequest`, `SubmitQuizRequest`, `SendFriendRequest`.

**Confidence:** HIGH -- this is standard Laravel convention documented in official Laravel docs.

### Pattern 2: Explicit API Resources (No Pass-Throughs)

**What:** Replace all `parent::toArray($request)` resource stubs with explicit field mappings. Every field in the JSON response is intentionally listed.

**When:** All 13 existing resources plus any new ones.

**Why:** Pass-throughs leak internal model structure (timestamps, soft-delete columns, pivot data). Explicit mapping controls the API contract, prevents accidental exposure, and makes the response shape self-documenting.

**Example:**

```php
// app/Http/Resources/ActionResource.php
class ActionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'domain' => $this->domain,
            'frequency' => $this->frequency,
            'alignment' => $this->alignment,
            'scheduled_time' => $this->scheduled_time,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Conditional relationship loading
            'check_ins' => ActionCheckInResource::collection($this->whenLoaded('checkIns')),
        ];
    }
}
```

**Key rule:** Use `$this->whenLoaded('relationship')` for conditional relationship inclusion. Never eager-load relationships in the resource itself.

**Confidence:** HIGH -- `TraitResource` already demonstrates this pattern in the codebase.

### Pattern 3: Service Layer for External API Integrations

**What:** Create a `ClaudeService` class that wraps the Anthropic API. The service handles prompt construction, API calls, response parsing, error handling, and logging. Controllers never interact with external APIs directly.

**When:** Any external API integration (Claude, OneSignal, future services).

**Why:** Isolates external dependencies, makes controllers testable (mock the service), centralizes error handling and retry logic, keeps API keys in config.

**Example structure:**

```php
// app/Services/ClaudeService.php
namespace App\Services;

use Anthropic\Client;

class ClaudeService
{
    private Client $client;

    public function __construct()
    {
        $this->client = \Anthropic\Client::factory()
            ->withApiKey(config('services.anthropic.api_key'))
            ->make();
    }

    public function generateSuggestions(array $context): array
    {
        // Build system prompt with user's vision, traits, actions
        // Call Messages API
        // Parse structured response
        // Return array of suggestion data
    }
}
```

**Config location:** `config/services.php` under `'anthropic'` key. API key in `.env` as `ANTHROPIC_API_KEY`.

**Confidence:** MEDIUM -- the official `anthropic-ai/sdk` package exists on Packagist but I could not verify its exact API surface from docs. The community `mozex/anthropic-laravel` package has 350k+ installs and is well-documented as an alternative.

### Pattern 4: Feature Test Organization by Domain

**What:** One test file per controller/domain area. Tests exercise the full HTTP stack using `actingAs()` with Sanctum and `RefreshDatabase`.

**When:** All new and existing critical endpoints.

**Why:** Feature tests catch integration issues (middleware, validation, database constraints) that unit tests miss. Organizing by domain keeps tests discoverable.

**Example structure:**

```
tests/Feature/
    Auth/
        AuthenticationTest.php
        GoogleOAuthTest.php
    Friends/
        FriendRequestTest.php
        FriendListTest.php
    Quizzes/
        QuizTest.php
    Reels/
        ReelTest.php
    Actions/
        ActionCrudTest.php
        ActionCheckInTest.php
        ActionStreakTest.php
    Suggestions/
        SuggestionGenerateTest.php
    Settings/
        SettingsExportTest.php
        SettingsLocaleTest.php
```

**Example test:**

```php
namespace Tests\Feature\Friends;

use App\Models\User;
use App\Models\Friend;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FriendRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_friend_request(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/friends/request', [
            'friend_id' => $friend->id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Request sent']);

        $this->assertDatabaseHas('friends', [
            'user_id' => $user->id,
            'friend_id' => $friend->id,
            'status' => 'pending',
        ]);
    }
}
```

**Prerequisites:** Model factories must exist. Currently only `UserFactory` exists -- factories are needed for `Action`, `Friend`, `Reel`, `Quiz`, `JournalEntry`, etc.

**Confidence:** HIGH -- standard Laravel testing patterns.

### Pattern 5: Route Group Organization

**What:** Group related routes under prefixed sub-groups within the `auth:sanctum` middleware group. Use consistent REST verb mapping.

**When:** Adding the three unrouted controller groups (Friends, Quizzes, Reels) and the new endpoints (Settings, Suggestions/generate).

**Example:**

```php
// Inside auth:sanctum group in routes/api.php

// Friends
Route::prefix('friends')->group(function () {
    Route::get('/', [FriendController::class, 'index']);
    Route::get('/requests', [FriendController::class, 'requests']);
    Route::get('/search', [FriendController::class, 'search']);
    Route::get('/{id}/profile', [FriendController::class, 'profile']);
    Route::post('/request', [FriendController::class, 'sendRequest']);
    Route::post('/{id}/accept', [FriendController::class, 'accept']);
    Route::post('/{id}/decline', [FriendController::class, 'decline']);
    Route::delete('/{id}', [FriendController::class, 'remove']);
});

// Quizzes
Route::prefix('quizzes')->group(function () {
    Route::get('/', [QuizController::class, 'index']);
    Route::post('/submit', [QuizController::class, 'submit']);
    Route::get('/history', [QuizController::class, 'history']);
});

// Reels
Route::prefix('reels')->group(function () {
    Route::get('/', [ReelController::class, 'index']);
    Route::post('/{id}/like', [ReelController::class, 'like']);
    Route::post('/{id}/save', [ReelController::class, 'save']);
});

// Settings (new)
Route::prefix('settings')->group(function () {
    Route::put('/locale', [SettingsController::class, 'updateLocale']);
    Route::get('/export', [SettingsController::class, 'export']);
});

// Suggestions (extend existing)
Route::post('/suggestions/generate', [SuggestionController::class, 'generate']);
```

**Important constraint:** Route paths must match what the React frontend already calls. Verify exact paths against frontend API calls before finalizing.

**Confidence:** HIGH -- follows existing codebase patterns.

## Anti-Patterns to Avoid

### Anti-Pattern 1: Pass-Through API Resources

**What:** Resources that use `parent::toArray($request)` to dump the entire model as JSON.

**Why bad:** Leaks internal columns (deleted_at, pivot data, internal flags), makes the API contract implicit rather than explicit, and any model column change silently changes the API response.

**Instead:** Always list fields explicitly. Use `$this->whenLoaded()` for relationships.

### Anti-Pattern 2: Business Logic in Controllers

**What:** Controllers that do more than validate-orchestrate-respond. Complex query logic, external API calls, multi-step business rules directly in controller methods.

**Why bad:** Untestable in isolation, duplicated across endpoints, makes controllers bloated.

**Instead:** Extract to Service classes (for external integrations) or Model scopes (for query logic). Controllers should be 10-20 lines per method.

**Current violation:** `FriendController::sendRequest()` calls `event()` and `->notify()` inline. The event dispatch is fine (lightweight), but the notification could be triggered by the event listener instead for better separation.

### Anti-Pattern 3: Missing Models Referenced by Controllers

**What:** Controllers that use `Friend::create()` and `ReelInteraction::create()` but these models and their migrations do not exist.

**Why bad:** The controllers will throw fatal errors when routed. This is the current state of `FriendController` (no `Friend` model, no `friends` migration) and `ReelController` (no `ReelInteraction` model, no `reel_interactions` migration).

**Instead:** Always create migration -> model -> controller -> route in dependency order.

### Anti-Pattern 4: SQLite `ilike` Usage

**What:** `FriendController::search()` uses `->where('name', 'ilike', ...)` which is PostgreSQL syntax. SQLite does not support `ilike`.

**Instead:** Use `->where('name', 'LIKE', ...)` which is case-insensitive for ASCII in SQLite by default, or use `->whereRaw('LOWER(name) LIKE ?', [strtolower($q)])` for cross-database compatibility.

### Anti-Pattern 5: Inline Validation in Controllers

**What:** Every controller method currently calls `$request->validate([...])` inline.

**Why bad:** Validation rules are buried in controller methods, not reusable, not independently testable, and bloat the controller.

**Instead:** Form Request classes (see Pattern 1 above). Migrate incrementally -- new endpoints use Form Requests, existing endpoints migrate as they're touched.

## Build Order (Dependency Chain)

The build order is driven by component dependencies. Each layer depends on the one before it.

```
Phase 1: Foundation (no dependencies)
    |
    +-- Migrations for missing tables (friends, reel_interactions)
    +-- Models for missing entities (Friend, ReelInteraction)
    +-- Model factories for all testable models
    |
Phase 2: Routing (depends on Phase 1 models)
    |
    +-- Route groups for Friends, Quizzes, Reels
    +-- Route definitions for Settings, Suggestions/generate
    +-- Fix FriendController SQLite compatibility (ilike -> LIKE)
    |
Phase 3: Service Layer (independent of Phase 2)
    |
    +-- ClaudeService for AI suggestion generation
    +-- config/services.php anthropic key
    +-- SuggestionController::generate() method
    |
Phase 4: Response Hardening (can start after Phase 2)
    |
    +-- Replace all pass-through API Resources with explicit field mappings
    +-- Ensure consistent response wrapping (data envelope where collections used)
    +-- Add missing resources (FriendResource, FriendRequestResource, etc.)
    |
Phase 5: Validation Hardening (can overlap with Phase 4)
    |
    +-- Create Form Request classes for new endpoints first
    +-- Migrate existing inline validation to Form Requests
    |
Phase 6: Tests (depends on all above)
    |
    +-- Model factories (if not done in Phase 1)
    +-- Feature tests per domain area
    +-- Test critical paths: auth, friends CRUD, quiz submission, reel interactions
    +-- Test new endpoints: streak, journal share, AI generate, settings
```

**Parallelization opportunities:**
- Phase 3 (Services) can run in parallel with Phase 2 (Routing)
- Phase 4 (Resources) and Phase 5 (Form Requests) can run in parallel
- Phase 6 (Tests) should come last as it validates everything above

**Critical dependency:** Phase 1 (migrations + models) MUST come first. The `FriendController` and `ReelController` will fail fatally without their backing models and tables. No other work on these controllers is meaningful until the database layer exists.

## Scalability Considerations

| Concern | Current (SQLite) | At 10K users | At 100K users |
|---------|-------------------|--------------|---------------|
| Database | SQLite is fine for dev/early prod | Migrate to PostgreSQL | PostgreSQL with read replicas |
| AI API calls | Synchronous in request | Queue via Laravel Jobs | Queue + rate limiting + caching recent suggestions |
| Friend search | `LIKE` query on users table | Add index on name column | Full-text search (Meilisearch/Algolia) |
| Reels pagination | `inRandomOrder()->paginate(10)` | Fine | Replace with cursor pagination + pre-shuffled ordering |
| Push notifications | Synchronous in some paths | Already queued via service | Fine |

**Note:** Scalability is explicitly out of scope for this milestone. The architecture supports future scaling but the current SQLite + synchronous approach is appropriate for the app's current stage.

## Sources

- Existing codebase analysis (controllers, models, routes, resources) -- PRIMARY SOURCE
- [Official Anthropic PHP SDK on Packagist](https://packagist.org/packages/anthropic-ai/sdk) -- MEDIUM confidence
- [mozex/anthropic-laravel community package](https://packagist.org/packages/mozex/anthropic-laravel) -- 350k+ installs, MEDIUM confidence
- [Claude PHP SDK Laravel](https://github.com/claude-php/Claude-PHP-SDK-Laravel) -- community maintained, MEDIUM confidence
- Laravel 12 documentation (Form Requests, API Resources, Feature Testing) -- HIGH confidence from training data

---

*Architecture analysis: 2026-03-27*
