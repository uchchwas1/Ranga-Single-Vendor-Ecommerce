<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_can_be_verified_via_signed_url(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('api.v1.auth.verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1((string) $user->email),
        ]);

        $this->getJson($url)->assertOk();

        $this->assertNotNull($user->fresh()?->email_verified_at);
    }

    public function test_verification_fails_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute('api.v1.auth.verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1('other@example.com'),
        ]);

        $this->getJson($url)->assertForbidden();

        $this->assertNull($user->fresh()?->email_verified_at);
    }

    public function test_verification_fails_without_signature(): void
    {
        $user = User::factory()->unverified()->create();

        $this->getJson('/api/v1/auth/verify-email/'.$user->id.'/'.sha1((string) $user->email))
            ->assertForbidden();
    }

    public function test_verification_email_can_be_resent(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/verify-email/resend')->assertOk();

        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_resend_is_a_no_op_for_verified_users(): void
    {
        Notification::fake();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/auth/verify-email/resend')->assertOk();

        Notification::assertNothingSent();
    }

    public function test_resend_requires_authentication(): void
    {
        $this->postJson('/api/v1/auth/verify-email/resend')->assertUnauthorized();
    }
}
