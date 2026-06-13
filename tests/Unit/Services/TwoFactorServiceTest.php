<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\Auth\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorServiceTest extends TestCase
{
    use RefreshDatabase;

    private TwoFactorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(TwoFactorService::class);
    }

    public function test_enable_stores_a_pending_secret_and_recovery_codes(): void
    {
        $user = User::factory()->create();

        $payload = $this->service->enable($user);

        $this->assertNotSame('', $payload['secret']);
        $this->assertCount(8, $payload['recovery_codes']);
        $this->assertStringContainsString('otpauth://totp/', $payload['otpauth_url']);
        $this->assertFalse($user->fresh()?->hasTwoFactorEnabled());
    }

    public function test_confirm_requires_a_valid_totp_code(): void
    {
        $user = User::factory()->create();
        $payload = $this->service->enable($user);

        $this->assertFalse($this->service->confirm($user, '000000'));

        $valid = app(Google2FA::class)->getCurrentOtp($payload['secret']);
        $this->assertTrue($this->service->confirm($user, $valid));
        $this->assertTrue($user->fresh()?->hasTwoFactorEnabled());
    }

    public function test_recovery_codes_are_single_use(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->assertTrue($this->service->redeemRecoveryCode($user, 'RECOVERY01'));
        $this->assertFalse($this->service->redeemRecoveryCode($user, 'RECOVERY01'));
        $this->assertTrue($this->service->redeemRecoveryCode($user, 'RECOVERY02'));
    }

    public function test_disable_clears_all_two_factor_state(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $this->service->disable($user);

        $fresh = $user->fresh();
        $this->assertNotNull($fresh);
        $this->assertNull($fresh->two_factor_secret);
        $this->assertNull($fresh->two_factor_recovery_codes);
        $this->assertFalse($fresh->hasTwoFactorEnabled());
    }
}
