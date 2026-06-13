<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\Enums\LoginStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'login@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('two_factor_required', false)
            ->assertJsonStructure(['token', 'user' => ['id', 'email']]);

        $this->assertNotNull($user->fresh()?->last_login_at);
        $this->assertDatabaseHas('login_activities', [
            'user_id' => $user->id,
            'status' => LoginStatus::Success->value,
        ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['email' => 'wrong@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'incorrect',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['email']);

        $this->assertDatabaseHas('login_activities', [
            'user_id' => $user->id,
            'status' => LoginStatus::Failed->value,
        ]);
    }

    public function test_login_fails_for_deactivated_account(): void
    {
        User::factory()->inactive()->create(['email' => 'inactive@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_two_factor_when_enabled(): void
    {
        User::factory()->withTwoFactor()->create(['email' => '2fa@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => '2fa@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('two_factor_required', true)
            ->assertJsonStructure(['challenge_token']);

        $this->assertArrayNotHasKey('token', $response->json());
    }

    public function test_login_validation_errors(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertUnprocessable()->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create(['email' => 'limited@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'limited@example.com',
                'password' => 'incorrect',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'limited@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(429);
    }
}
