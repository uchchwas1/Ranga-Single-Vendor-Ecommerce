<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Jobs\Auth\SendPasswordResetEmail;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_queues_the_reset_job(): void
    {
        Queue::fake();

        User::factory()->create(['email' => 'forgot@example.com']);

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'forgot@example.com'])
            ->assertOk();

        Queue::assertPushed(SendPasswordResetEmail::class);
    }

    public function test_forgot_password_responds_generically_for_unknown_email(): void
    {
        Queue::fake();

        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'ghost@example.com'])
            ->assertOk();
    }

    public function test_forgot_password_requires_valid_email(): void
    {
        $this->postJson('/api/v1/auth/forgot-password', ['email' => 'nope'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_reset_job_sends_the_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'job@example.com']);

        (new SendPasswordResetEmail('job@example.com'))->handle();

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $token = Password::createToken($user);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => $token,
            'email' => 'reset@example.com',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-secret-password', (string) $user->fresh()?->password));
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        User::factory()->create(['email' => 'badtoken@example.com']);

        $this->postJson('/api/v1/auth/reset-password', [
            'token' => 'invalid-token',
            'email' => 'badtoken@example.com',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }
}
