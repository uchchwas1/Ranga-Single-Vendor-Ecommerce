<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Events\Auth\UserRegistered;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(AuthService::class);
    }

    public function test_register_creates_a_user_with_unique_referral_code(): void
    {
        Event::fake([UserRegistered::class]);

        $user = $this->service->register([
            'name' => 'Service User',
            'email' => 'service@example.com',
            'password' => 'secret-password',
        ]);

        $this->assertSame(10, strlen($user->referral_code));
        $this->assertSame('bn', $user->locale);
        $this->assertSame('Asia/Dhaka', $user->timezone);

        Event::assertDispatched(UserRegistered::class);
    }

    public function test_attempt_login_throws_for_unknown_email(): void
    {
        $this->expectException(ValidationException::class);

        $this->service->attemptLogin('missing@example.com', 'whatever');
    }

    public function test_attempt_login_returns_token_for_valid_credentials(): void
    {
        User::factory()->create(['email' => 'svc-login@example.com']);

        $result = $this->service->attemptLogin('svc-login@example.com', 'password');

        $this->assertFalse($result->requiresTwoFactor());
        $this->assertNotNull($result->token);
        $this->assertNotNull($result->user);
    }

    public function test_attempt_login_issues_challenge_for_two_factor_users(): void
    {
        User::factory()->withTwoFactor()->create(['email' => 'svc-2fa@example.com']);

        $result = $this->service->attemptLogin('svc-2fa@example.com', 'password');

        $this->assertTrue($result->requiresTwoFactor());
        $this->assertNull($result->token);
        $this->assertNotNull($result->challengeToken);
        $this->assertDatabaseCount('two_factor_challenges', 1);
    }
}
