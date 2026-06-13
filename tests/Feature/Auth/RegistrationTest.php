<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Jobs\Auth\SendEmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_register_with_valid_data(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Anika Rahman',
            'email' => 'anika@example.com',
            'phone' => '01712345678',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'anika@example.com')
            ->assertJsonPath('data.email_verified', false)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'referral_code'], 'message']);

        $this->assertDatabaseHas('users', ['email' => 'anika@example.com', 'phone' => '01712345678']);

        Queue::assertPushed(SendEmailVerification::class);
    }

    public function test_registration_links_referrer_via_referral_code(): void
    {
        Queue::fake();

        $referrer = User::factory()->create();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Refered Friend',
            'email' => 'friend@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'referral_code' => $referrer->referral_code,
        ]);

        $response->assertCreated();

        $created = User::query()->where('email', 'friend@example.com')->firstOrFail();
        $this->assertSame($referrer->id, $created->referred_by);
    }

    public function test_registration_fails_with_invalid_payload(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Dup',
            'email' => 'taken@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['email']);
    }

    public function test_registration_fails_with_invalid_bd_phone(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Bad Phone',
            'email' => 'badphone@example.com',
            'phone' => '0123456',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['phone']);
    }
}
