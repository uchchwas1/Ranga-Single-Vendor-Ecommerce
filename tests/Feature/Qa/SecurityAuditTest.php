<?php

declare(strict_types=1);

namespace Tests\Feature\Qa;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_applied(): void
    {
        $response = $this->getJson('/api/v1/settings');

        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_api_routes_are_rate_limited(): void
    {
        $this->getJson('/api/v1/settings')->assertHeader('X-RateLimit-Limit');
    }

    public function test_search_is_injection_safe(): void
    {
        Product::factory()->create(['name' => 'Genuine Saree']);

        // A SQL-injection style payload must not error or leak rows.
        $this->getJson('/api/v1/search?q='.urlencode("' OR 1=1 --"))
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_passwords_are_hashed_and_never_exposed(): void
    {
        $user = User::factory()->create();

        $this->assertNotSame('password', $user->password);
        $this->assertTrue(Hash::check('password', $user->password));

        Sanctum::actingAs($user);
        $this->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonMissingPath('data.password');
    }

    public function test_two_factor_secrets_are_never_serialised(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->assertArrayNotHasKey('two_factor_secret', $user->toArray());
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $user->toArray());
    }
}
