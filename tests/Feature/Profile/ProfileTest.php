<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_view_their_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'locale', 'timezone', 'referral_code']]);
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
    }

    public function test_a_user_can_update_their_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile', [
            'name' => 'New Name',
            'phone' => '01812345678',
            'locale' => 'en',
        ])->assertOk()->assertJsonPath('data.name', 'New Name');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'phone' => '01812345678',
            'locale' => 'en',
        ]);
    }

    public function test_profile_update_validates_phone_format(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/v1/profile', ['phone' => '12345'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }
}
