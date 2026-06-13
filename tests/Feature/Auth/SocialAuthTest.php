<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as OAuthUser;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Stub the Socialite driver to return the given OAuth user.
     */
    private function mockProvider(OAuthUser $oauthUser): void
    {
        $provider = Mockery::mock(Provider::class, function (MockInterface $mock) use ($oauthUser): void {
            $mock->shouldReceive('stateless')->andReturnSelf();
            $mock->shouldReceive('userFromToken')->andReturn($oauthUser);
        });

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    private function makeOAuthUser(string $id, string $email, string $name): OAuthUser
    {
        $user = new OAuthUser();
        $user->id = $id;
        $user->email = $email;
        $user->name = $name;
        $user->token = 'provider-token';

        return $user;
    }

    public function test_a_new_user_is_provisioned_from_google(): void
    {
        $this->mockProvider($this->makeOAuthUser('999001', 'social@example.com', 'Social User'));

        $response = $this->postJson('/api/v1/auth/social/google', ['access_token' => 'valid-token']);

        $response->assertOk()->assertJsonStructure(['token', 'user' => ['id', 'email']]);

        $user = User::query()->where('email', 'social@example.com')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '999001',
        ]);
    }

    public function test_an_existing_social_account_logs_straight_in(): void
    {
        $user = User::factory()->create();
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '999002',
        ]);

        $this->mockProvider($this->makeOAuthUser('999002', (string) $user->email, (string) $user->name));

        $response = $this->postJson('/api/v1/auth/social/google', ['access_token' => 'valid-token']);

        $response->assertOk()->assertJsonPath('user.id', $user->id);

        $this->assertSame(1, SocialAccount::query()->count());
    }

    public function test_social_login_validates_the_payload(): void
    {
        $this->postJson('/api/v1/auth/social/google', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['access_token']);
    }

    public function test_unknown_providers_are_rejected(): void
    {
        $this->postJson('/api/v1/auth/social/twitter', ['access_token' => 'x'])
            ->assertNotFound();
    }
}
