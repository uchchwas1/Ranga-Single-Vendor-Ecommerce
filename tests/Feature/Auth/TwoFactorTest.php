<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_begin_two_factor_enrolment(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/auth/2fa/enable');

        $response->assertOk()
            ->assertJsonStructure(['secret', 'otpauth_url', 'recovery_codes']);

        $this->assertCount(8, $response->json('recovery_codes'));
    }

    public function test_enrolment_can_be_confirmed_with_valid_totp(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $secret = $this->postJson('/api/v1/auth/2fa/enable')->json('secret');

        $code = app(Google2FA::class)->getCurrentOtp($secret);

        $this->postJson('/api/v1/auth/2fa/confirm', ['code' => $code])->assertOk();

        $this->assertTrue($user->fresh()?->hasTwoFactorEnabled());
    }

    public function test_enrolment_confirmation_fails_with_invalid_code(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/auth/2fa/enable');

        $this->postJson('/api/v1/auth/2fa/confirm', ['code' => '000000'])
            ->assertUnprocessable();
    }

    public function test_two_factor_challenge_can_be_completed_with_totp(): void
    {
        $secret = app(Google2FA::class)->generateSecretKey();
        User::factory()->withTwoFactor($secret)->create(['email' => 'totp@example.com']);

        $challengeToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'totp@example.com',
            'password' => 'password',
        ])->json('challenge_token');

        $response = $this->postJson('/api/v1/auth/2fa/verify', [
            'challenge_token' => $challengeToken,
            'code' => app(Google2FA::class)->getCurrentOtp($secret),
        ]);

        $response->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_two_factor_challenge_accepts_a_recovery_code_once(): void
    {
        $user = User::factory()->withTwoFactor()->create(['email' => 'recovery@example.com']);

        $challengeToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'recovery@example.com',
            'password' => 'password',
        ])->json('challenge_token');

        $this->postJson('/api/v1/auth/2fa/verify', [
            'challenge_token' => $challengeToken,
            'code' => 'RECOVERY01',
        ])->assertOk();

        $this->assertNotContains('RECOVERY01', $user->fresh()?->two_factor_recovery_codes ?? []);
    }

    public function test_two_factor_challenge_rejects_invalid_code(): void
    {
        User::factory()->withTwoFactor()->create(['email' => 'invalid2fa@example.com']);

        $challengeToken = $this->postJson('/api/v1/auth/login', [
            'email' => 'invalid2fa@example.com',
            'password' => 'password',
        ])->json('challenge_token');

        $this->postJson('/api/v1/auth/2fa/verify', [
            'challenge_token' => $challengeToken,
            'code' => '000000',
        ])->assertUnprocessable()->assertJsonValidationErrors(['code']);
    }

    public function test_two_factor_endpoints_require_authentication(): void
    {
        $this->postJson('/api/v1/auth/2fa/enable')->assertUnauthorized();
        $this->postJson('/api/v1/auth/2fa/disable')->assertUnauthorized();
    }

    public function test_two_factor_can_be_disabled(): void
    {
        $user = User::factory()->withTwoFactor()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/2fa/disable')->assertOk();

        $this->assertFalse($user->fresh()?->hasTwoFactorEnabled());
    }
}
