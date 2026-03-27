# Stack Research

**Domain:** Laravel API backend hardening -- Claude AI integration, API resources, testing, database-driven content
**Researched:** 2026-03-27
**Confidence:** HIGH

## Recommended Stack

### Core Technologies

These are additions to the existing stack (Laravel 12, Sanctum, Reverb, Filament v5 are already in place).

| Technology | Version | Purpose | Why Recommended |
|------------|---------|---------|-----------------|
| `mozex/anthropic-laravel` | ^1.3 | Claude API integration with Laravel Facade | Laravel-native: publishes config, provides Facade, has built-in test faking via `Anthropic::fake()`. Covers messages, streaming, tool use, extended thinking, token counting. Better than raw HTTP because it handles retries, error mapping, and response objects. Better than Prism/Laravel AI SDK for this project because we only need Anthropic and want full API coverage, not a provider-abstraction layer. |
| PHPUnit | 11.5 (already installed) | Feature testing | Already in the project. Switching to Pest would add churn for no benefit -- the project has zero tests, so there is no existing style to maintain. PHPUnit is perfectly adequate for API feature tests and avoids adding another dependency. |

### Supporting Libraries

| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| `anthropic-ai/sdk` | ^0.8 | Underlying PHP SDK (auto-installed by mozex/anthropic-laravel) | Do not install directly -- `mozex/anthropic-laravel` wraps this and adds Laravel config, Facade, and test helpers. Listed here for awareness only. |
| `brianium/paratest` | ^7.7 | Parallel test execution | Install as dev dependency when test suite exceeds 30 seconds. Not needed initially with a handful of feature tests. |

### Development Tools

| Tool | Purpose | Notes |
|------|---------|-------|
| Laravel Pint | Code formatting (already installed) | Run after generating test files to maintain PSR-12 consistency |
| `php artisan make:test` | Generate feature test stubs | Use `--pest` flag only if migrating to Pest (not recommended) |
| `php artisan make:resource` | Generate API Resource classes | Already have 14 resource classes -- just need to fill in `toArray()` methods |
| `php artisan make:seeder` | Generate seeder classes | Use for suggestions migration from hardcoded array to database |
| `php artisan make:request` | Generate Form Request classes | Use for validation extraction from controllers (hardening) |

## Installation

```bash
# Claude API integration
composer require mozex/anthropic-laravel

# Publish config (creates config/anthropic.php)
php artisan vendor:publish --tag=anthropic-config

# Add to .env
ANTHROPIC_API_KEY=sk-ant-...
```

No other new packages are needed. The existing stack already includes everything required for API resources, testing, and seeders.

## Alternatives Considered

| Recommended | Alternative | When to Use Alternative |
|-------------|-------------|-------------------------|
| `mozex/anthropic-laravel` | `prism-php/prism` (v0.100) | If you need to switch between multiple LLM providers (OpenAI, Ollama, etc.) or want provider abstraction. Prism is the mature community standard for multi-provider LLM in Laravel. Not needed here -- we only use Anthropic. |
| `mozex/anthropic-laravel` | `laravel/ai` (v0.4.1) | Official Laravel AI SDK, released Feb 2026. Still early (v0.4), requires PHP 8.3+, and adds abstraction overhead. Choose this if you want first-party Laravel support and are willing to accept a less mature API. Not recommended yet for production. |
| `mozex/anthropic-laravel` | Raw `Http::post()` to Anthropic API | If you want zero dependencies and only make simple message requests. Loses retries, streaming, error types, test faking. Not worth the savings for a real integration. |
| PHPUnit (existing) | Pest PHP (v3.x) | If starting a greenfield project or if the team strongly prefers Pest's closure syntax. Pest is excellent but adds a dependency and different syntax for no practical gain here -- the project has zero existing tests. |
| `brianium/paratest` | `--parallel` flag (built into artisan test) | Laravel's `artisan test --parallel` already uses Paratest under the hood. Install Paratest when needed; the artisan flag is the same thing. |

## What NOT to Use

| Avoid | Why | Use Instead |
|-------|-----|-------------|
| `claude-php/Claude-PHP-SDK-Laravel` | Unofficial, low maintenance, last updated mid-2024, minimal downloads | `mozex/anthropic-laravel` -- actively maintained, same-day Anthropic API feature support |
| `alle-ai/anthropic-api-php` | Low-level, no Laravel integration, no test helpers | `mozex/anthropic-laravel` |
| `laravel/ai` for production use | v0.4.1 as of March 2026 -- still pre-1.0, API may change, requires PHP 8.3+ (project uses 8.2+ compat) | `mozex/anthropic-laravel` for Anthropic-only usage |
| Direct cURL/Guzzle calls to Anthropic | No retry logic, no streaming support, no response typing, no test faking | `mozex/anthropic-laravel` |
| Pest PHP migration | Adding Pest to a PHPUnit project with zero tests creates unnecessary churn. Pest is great for new projects but the migration tooling and dual-syntax confusion aren't worth it here. | PHPUnit (already installed and configured) |
| `parent::toArray($request)` in API Resources | Pass-through resources expose every model attribute including timestamps, pivot data, and sensitive fields. This is the current tech debt. | Explicit field listing in `toArray()` with `$this->whenLoaded()` for relationships |

## Architecture Decisions

### Claude API Integration Pattern

**Use a dedicated Service class, not direct Facade calls in controllers.**

```
Controller -> SuggestionService -> Anthropic Facade -> Claude API
```

Why: The `SuggestionService` encapsulates prompt construction, response parsing, caching, and fallback logic. Controllers stay thin. The service is independently testable by faking the Anthropic Facade.

**Key config values for `config/anthropic.php`:**
- Model: `claude-sonnet-4-20250514` (fast, cost-effective for suggestion generation -- does not need Opus-level reasoning)
- Max tokens: 1024 (suggestions are short structured responses)
- Temperature: 0.7 (creative but consistent)

**Cost control:**
- Cache AI suggestions per user for 24 hours (use Laravel's cache with user-specific key)
- Limit generation to 1 request per user per hour via rate limiting
- Use structured output (JSON mode) to avoid parsing failures

### API Resource Transformation Pattern

**Explicit field listing with conditional relationships:**

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'title' => $this->title,
        'created_at' => $this->created_at?->toISOString(),
        'user' => new UserResource($this->whenLoaded('user')),
        'comments_count' => $this->whenCounted('comments'),
    ];
}
```

Rules:
1. Never use `parent::toArray()` -- always list fields explicitly
2. Use `whenLoaded()` for all relationships to prevent N+1
3. Use `whenCounted()` for counts
4. Format dates as ISO 8601 strings
5. Match the exact field names the frontend expects (the API contract is fixed)

### Feature Testing Pattern

**Use PHPUnit with `RefreshDatabase` and `actingAs` for Sanctum auth:**

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class FriendsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_send_friend_request(): void
    {
        Sanctum::actingAs($user = User::factory()->create());
        $friend = User::factory()->create();

        $response = $this->postJson('/api/friends/request', [
            'user_id' => $friend->id,
        ]);

        $response->assertStatus(201);
    }
}
```

Rules:
1. One test class per controller/feature area
2. Use `Sanctum::actingAs()` not manual token creation
3. Use `assertJson()` to verify response shape matches frontend expectations
4. Use `RefreshDatabase` trait (SQLite in-memory per phpunit.xml -- already configured)
5. Test both success and error paths (401, 403, 404, 422)
6. Use model factories for test data -- create factories for any model that lacks one

### Database-Driven Suggestions Pattern

**Migration + Seeder + Model replaces hardcoded array:**

```
suggestions table:
- id (uuid)
- title (string)
- description (text)
- domain (string: spiritual, knowledge, family, health, community, professional)
- category (string: Legacy, Community, Spiritual, Growth, Family)
- impact (enum: low, medium, high)
- effort (enum: low, medium, high)
- vision_connection (text)
- ai_reasoning (text)
- is_active (boolean, default true)
- locale (string, default 'en')
- timestamps
```

The seeder migrates the 12 existing hardcoded suggestions. The `is_active` flag allows admin management via Filament without deletion. The `locale` column supports future Malay translations.

## Version Compatibility

| Package A | Compatible With | Notes |
|-----------|-----------------|-------|
| `mozex/anthropic-laravel` ^1.3 | Laravel 12.x, PHP 8.2+ | Tested with Laravel 11.29+, 12.12+, and 13.0+ |
| `mozex/anthropic-laravel` ^1.3 | `anthropic-ai/sdk` ^0.8 | Auto-required; do not pin separately |
| PHPUnit 11.5 | Laravel 12.x | Already installed and working |
| Filament v5.2 | Laravel 12.x | Already installed; Filament resource for Suggestion model is straightforward |

## Sources

- [anthropic-ai/sdk on Packagist](https://packagist.org/packages/anthropic-ai/sdk) -- v0.8.0, official Anthropic PHP SDK (HIGH confidence)
- [mozex/anthropic-laravel on Packagist](https://packagist.org/packages/mozex/anthropic-laravel) -- v1.3.3, Laravel wrapper (HIGH confidence)
- [mozex/anthropic-laravel on GitHub](https://github.com/mozex/anthropic-laravel) -- Facade, config, test faking docs (HIGH confidence)
- [prism-php/prism on Packagist](https://packagist.org/packages/prism-php/prism) -- v0.100.1, multi-provider alternative (HIGH confidence)
- [laravel/ai on Packagist](https://packagist.org/packages/laravel/ai) -- v0.4.1, official Laravel AI SDK (HIGH confidence)
- [Laravel 12.x Eloquent API Resources docs](https://laravel.com/docs/12.x/eloquent-resources) -- Official resource transformation patterns (HIGH confidence)
- [Laravel 12.x HTTP Tests docs](https://laravel.com/docs/12.x/http-tests) -- Feature testing patterns (HIGH confidence)
- [Laravel 12.x Testing docs](https://laravel.com/docs/12.x/testing) -- Test setup and RefreshDatabase (HIGH confidence)

---
*Stack research for: Laravel API backend hardening*
*Researched: 2026-03-27*
