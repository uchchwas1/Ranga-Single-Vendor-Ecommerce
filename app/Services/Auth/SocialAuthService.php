<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserRegistered;
use App\Models\SocialAccount;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Support\Dto\AuthResult;
use App\Support\Enums\SocialProvider;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as OAuthUser;
use Throwable;

/**
 * Application service for OAuth (Google / Facebook) authentication.
 */
class SocialAuthService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly UserRepositoryContract $users,
    ) {
    }

    /**
     * Authenticate using a provider-issued OAuth access token.
     *
     * @throws ValidationException
     */
    public function loginWithToken(SocialProvider $provider, string $accessToken, ?string $ip = null, ?string $userAgent = null): AuthResult
    {
        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver($provider->value);

            /** @var OAuthUser $oauthUser */
            $oauthUser = $driver->stateless()->userFromToken($accessToken);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'access_token' => [__('auth.social.token_invalid')],
            ]);
        }

        $user = $this->resolveUser($provider, $oauthUser);

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'access_token' => [__('auth.inactive')],
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        $this->users->recordLogin($user, $ip);

        UserLoggedIn::dispatch($user, $ip, $userAgent);

        return AuthResult::authenticated($user->refresh(), $token);
    }

    /**
     * Find or provision the local user for an OAuth identity.
     */
    private function resolveUser(SocialProvider $provider, OAuthUser $oauthUser): User
    {
        return DB::transaction(function () use ($provider, $oauthUser): User {
            $account = SocialAccount::query()
                ->where('provider', $provider->value)
                ->where('provider_id', (string) $oauthUser->getId())
                ->first();

            if ($account !== null) {
                $account->forceFill([
                    'token' => $oauthUser->token,
                    'refresh_token' => $oauthUser->refreshToken,
                    'avatar' => $oauthUser->getAvatar(),
                ])->save();

                /** @var User */
                return $account->user()->firstOrFail();
            }

            $email = $oauthUser->getEmail();

            if ($email === null || $email === '') {
                throw ValidationException::withMessages([
                    'access_token' => [__('auth.social.email_missing')],
                ]);
            }

            $user = $this->users->findByEmail($email);

            if ($user === null) {
                $user = $this->users->create([
                    'name' => $oauthUser->getName() ?? __('auth.social.default_name'),
                    'email' => $email,
                    'password' => Str::random(40),
                    'avatar' => $oauthUser->getAvatar(),
                    'locale' => (string) config('ranga.defaults.locale'),
                    'timezone' => (string) config('ranga.defaults.timezone'),
                    'referral_code' => $this->generateReferralCode(),
                ]);

                $user->forceFill(['email_verified_at' => Date::now()])->save();

                UserRegistered::dispatch($user);
            }

            SocialAccount::query()->create([
                'user_id' => $user->id,
                'provider' => $provider->value,
                'provider_id' => (string) $oauthUser->getId(),
                'token' => $oauthUser->token,
                'refresh_token' => $oauthUser->refreshToken,
                'avatar' => $oauthUser->getAvatar(),
            ]);

            return $user->refresh();
        });
    }

    /**
     * Generate a unique referral code.
     */
    private function generateReferralCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while ($this->users->findByReferralCode($code) !== null);

        return $code;
    }
}
