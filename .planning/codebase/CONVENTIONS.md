# Coding Conventions

**Analysis Date:** 2026-03-27

## Naming Patterns

**Files:**
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

**Functions/Methods:**
- camelCase for all methods — `store()`, `checkIn()`, `findOrCreateGoogleUser()`
- Private helpers prefixed with visibility keyword — `private function findOrCreateGoogleUser()`
- Eloquent relationship methods use camelCase matching relation type — `hasMany`, `belongsTo`

**Variables:**
- camelCase — `$visionId`, `$googleUser`, `$checkIn`
- Snake_case in arrays passed to DB operations — `['user_id' => $userId]`

**Types:**
- PHP 8.2+ typed properties and return types used in new code
- `string` type hints for UUID route parameters — `public function update(Request $request, string $id)`
- Return type hints on service methods — `public function sendToUser(...): array`

**Database:**
- Table names: snake_case plural — `action_check_ins`, `vision_versions`, `community_posts`
- Column names: snake_case — `user_id`, `check_in_date`, `display_name`
- UUID primary keys on all tables via `$table->uuid('id')->primary()`
- Foreign keys use `foreignUuid()` — `$table->foreignUuid('user_id')->constrained()->cascadeOnDelete()`

## Code Style

**Formatting:**
- Laravel Pint (PSR-12): `./vendor/bin/pint`
- No explicit `.pint.json` config found — uses Pint defaults (Laravel preset)

**Linting:**
- No separate ESLint config detected; Pint handles PHP formatting

**Density:**
- Controllers are compact: single-line inline-ifs are common for query filtering
  ```php
  if ($request->has('domain')) $query->where('domain', $request->domain);
  ```
- Models are kept minimal — all on one or few lines when simple:
  ```php
  public function user() { return $this->belongsTo(User::class); }
  ```
- More complex models (e.g., `Trait_.php`) use expanded multi-line style for readability

## Import Organization

**Order:**
1. Namespace declaration (no blank line after `<?php`)
2. `use` statements (no blank line before)
3. Class declaration

**Style:**
- No blank line between `<?php` and `namespace` in most files — compact header style
- Imports grouped loosely: framework classes, then app classes (no enforced blank-line separation between groups)
- Example from `ActionController.php`:
  ```php
  <?php
  namespace App\Http\Controllers\Api;

  use App\Http\Controllers\Controller;
  use App\Models\Action;
  use App\Events\ActionCompleted;
  use App\Models\ActionCheckIn;
  use App\Models\TimelineEvent;
  use Illuminate\Http\Request;
  ```

**Path Aliases:**
- PSR-4 autoloading only — no custom path aliases
- `App\` → `app/`, `Database\Factories\` → `database/factories/`, `Tests\` → `tests/`

## Error Handling

**Patterns:**
- Validation errors: use `$request->validate([...])` which throws `ValidationException` automatically — Laravel handles 422 response
- Manual validation error: `throw ValidationException::withMessages(['field' => ['message']])` (used in `AuthController`)
- Not-found errors: use `->findOrFail($id)` on relationship queries — Laravel returns 404 automatically
- External API errors: wrap in `try/catch (\Exception $e)` and return JSON error response manually:
  ```php
  try {
      $googleUser = Socialite::driver('google')->stateless()->user();
  } catch (\Exception $e) {
      return response()->json(['error' => 'Google authentication failed.'], 401);
  }
  ```
- Service errors: catch `\Exception`, log with `Log::error(...)`, return structured error array:
  ```php
  Log::error("OneSignal error: " . $e->getMessage());
  return ["success" => false, "reason" => $e->getMessage()];
  ```
- No global exception handler customization detected beyond Laravel defaults

**HTTP Status Codes:**
- `201` for resource creation: `return response()->json($resource, 201);`
- `200` (default) for reads and updates
- `401` for authentication failures
- `404` via `findOrFail()` (automatic)
- `422` via `$request->validate()` (automatic)

## Logging

**Framework:** Laravel `Log` facade

**Patterns:**
- Used only for service-level errors — `Log::error("OneSignal error: " . $e->getMessage())`
- Controllers do not log directly
- No structured log context arrays observed — plain string messages only

## Comments

**When to Comment:**
- PHPDoc blocks used for public service methods and non-obvious private helpers:
  ```php
  /**
   * Validate Google ID token from mobile/SPA and return Sanctum token.
   */
  public function googleToken(Request $request)
  ```
- Inline comments used for business logic intent, not obvious code:
  ```php
  // Overload warning
  // Create version snapshot before update
  // Remove expired subscriptions
  ```
- Many methods (especially simple CRUD) have no comments

**JSDoc/TSDoc:**
- Not applicable (PHP-only backend)

## Function Design

**Size:**
- Controller methods are generally short (10-30 lines)
- Longer methods acceptable when handling multiple related operations (e.g., `checkIn()` in `ActionController` is ~30 lines)
- Private helpers extracted for reused logic — e.g., `findOrCreateGoogleUser()` in `AuthController`

**Parameters:**
- Controller methods always receive `Request $request` as first parameter
- Route model binding not used — IDs are `string $id` parameters resolved manually via `findOrFail()`

**Return Values:**
- Controllers always return `response()->json(...)` or an API Resource
- Service methods return structured arrays: `["success" => bool, ...]`
- Boolean toggle endpoints return `['liked' => true/false]`
- Delete endpoints return `['message' => 'Deleted']`

## Module Design

**Exports:**
- All classes use namespaced autoloading — no explicit exports
- Controllers in `App\Http\Controllers\Api\` namespace

**Barrel Files:**
- Not used — Laravel routes file imports individual controller classes

## Model Conventions

**Required Traits:**
- All models use `App\Traits\HasUuid` for UUID primary keys
- User-owned models use `SoftDeletes` when deletable via API (e.g., `Action`, `JournalEntry`, `CommunityPost`)
- Models without soft deletes: `Trait_`, `ActionCheckIn`, `CheckIn`, `UserTrait`

**Fillable:**
- Explicit `$fillable` array required on all models — no mass-assignment `$guarded = []`

**Casts:**
- Defined via `protected function casts(): array` (PHP 8 method syntax, not property)
- Booleans always cast explicitly: `'is_active' => 'boolean'`
- DateTime fields cast to `'datetime'` or specific format: `'scheduled_time' => 'datetime:H:i'`

**Relationships:**
- Methods return Eloquent relation objects, no type hints on relationship methods
- Explicit foreign key only when non-conventional: `$this->hasMany(UserTrait::class, 'trait_id')`

## Resource Conventions

**API Resources:**
- Every model exposed via API has a corresponding Resource in `app/Http/Resources/`
- Most resources use `parent::toArray($request)` (passthrough) or explicit field mapping
- `TraitResource` shows explicit mapping pattern with localization:
  ```php
  return [
      'name' => $this->localized_name,
      'description' => $this->localized_description,
      ...
  ];
  ```
- Several resources (e.g., `ActionResource`, `CommunityPostResource`) are stubs using `parent::toArray()`

## Localization

**Pattern:**
- Models with translated content have `*_ms` columns alongside English columns
- Localized accessors follow `getLocalized{Field}Attribute()` naming convention
- Resources use `$this->localized_*` to access localized values
- Language strings in `lang/ms/` (array-based) and `lang/ms.json` (key-based)

---

*Convention analysis: 2026-03-27*
