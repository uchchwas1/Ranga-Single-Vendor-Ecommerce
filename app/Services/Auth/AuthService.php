<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Events\Auth\LoginFailed;
use App\Events\Auth\PasswordResetRequested;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserRegistered;
use App\Models\TwoFactorChallenge;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Support\Dto\AuthResult;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Application service handling registration, login, logout and
 * password reset flows. Controllers must only talk to this class.
 */
class AuthService
{
    private const int CHALLENGE_TTL_MINUTES = 5;

    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly UserRepositoryContract $users,
        private readonly TwoFactorService $twoFactor,
    ) {
    }

    /**
     * Register a new user account.
     *
     * @param  array{name: string, email: string, phone?: string|null, password: string, locale?: string|null, referral_code?: string|null}  $data
     */
    public function register(array $data): User
    {
        $referrer = isset($data['referral_code']) && $data['referral_code'] !== null && $data['referral_code'] !== ''
            ? $this->users->findByReferralCode($data['referral_code'])
            : null;

        $user = $this->users->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'locale' => $data['locale'] ?? (string) config('ranga.defaults.locale'),
            'timezone' => (string) config('ranga.defaults.timezone'),
            'referral_code' => $this->generateReferralCode(),
            'referred_by' => $referrer?->id,
        ]);

        UserRegistered::dispatch($user);

        return $user;
    }

    /**
     * Attempt a credential login.
     *
     * Returns an authenticated result, or a two-factor challenge when
     * the account has TOTP enabled.
     *
     * @throws ValidationException
     */
    public function attemptLogin(string $email, string $password, ?string $ip = null, ?string $userAgent = null): AuthResult
    {
        $user = $this->users->findByEmail($email);

        if ($user === null || ! Hash::check($password, $user->password)) {
            LoginFailed::dispatch($user?->id, $ip, $userAgent);

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        if (! $user->is_active) {
            LoginFailed::dispatch($user->id, $ip, $userAgent);

            throw ValidationException::withMessages([
                'email' => [__('auth.inactive')],
            ]);
        }

        if ($user->hasTwoFactorEnabled()) {
            return AuthResult::twoFactorRequired($this->issueChallenge($user));
        }

        return $this->authenticate($user, $ip, $userAgent);
    }

    /**
     * Complete a pending two-factor challenge with a TOTP or recovery code.
     *
     * @throws ValidationException
     */
    public function verifyTwoFactorChallenge(string $challengeToken, string $code, ?string $ip = null, ?string $userAgent = null): AuthResult
    {
        $challenge = TwoFactorChallenge::query()
            ->where('code', hash('sha256', $challengeToken))
            ->first();

        if ($challenge === null || ! $challenge->isUsable()) {
            throw ValidationException::withMessages([
                'challenge_token' => [__('auth.2fa.challenge_invalid')],
            ]);
        }

        /** @var User $user */
        $user = $challenge->user()->firstOrFail();

        $valid = $this->twoFactor->verify($user, $code)
            || $this->twoFactor->redeemRecoveryCode($user, $code);

        if (! $valid) {
            LoginFailed::dispatch($user->id, $ip, $userAgent);

            throw ValidationException::withMessages([
                'code' => [__('auth.2fa.code_invalid')],
            ]);
        }

        $challenge->forceFill(['used_at' => Date::now()])->save();

        return $this->authenticate($user, $ip, $userAgent);
    }

    /**
     * Revoke the token used for the current request.
     */
    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token instanceof \Laravel\Sanctum\PersonalAccessToken) {
            $token->delete();
        }
    }

    /**
     * Queue delivery of a password reset link.
     *
     * Always succeeds silently to avoid account enumeration.
     */
    public function sendPasswordResetLink(string $email): void
    {
        PasswordResetRequested::dispatch($email);
    }

    /**
     * Reset a password using a broker token.
     *
     * @param  array{email: string, password: string, password_confirmation: string, token: string}  $credentials
     *
     * @throws ValidationException
     */
    public function resetPassword(array $credentials): void
    {
        /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
        $broker = Password::broker();

        $user = $broker->getUser(['email' => $credentials['email']]);

        if (! $user instanceof User || ! $broker->tokenExists($user, $credentials['token'])) {
            throw ValidationException::withMessages([
                'email' => [__(Password::INVALID_TOKEN)],
            ]);
        }

        $user->forceFill([
            'password' => $credentials['password'],
            'remember_token' => Str::random(60),
        ])->save();

        $broker->deleteToken($user);

        event(new PasswordReset($user));
    }

    /**
     * Issue a Sanctum token and record login metadata.
     */
    private function authenticate(User $user, ?string $ip, ?string $userAgent): AuthResult
    {
        $token = $user->createToken('api')->plainTextToken;

        $this->users->recordLogin($user, $ip);

        UserLoggedIn::dispatch($user, $ip, $userAgent);

        return AuthResult::authenticated($user->refresh(), $token);
    }

    /**
     * Create a short-lived two-factor challenge for the user.
     */
    private function issueChallenge(User $user): string
    {
        $plain = Str::random(64);

        TwoFactorChallenge::query()->create([
            'user_id' => $user->id,
            'code' => hash('sha256', $plain),
            'expires_at' => Date::now()->addMinutes(self::CHALLENGE_TTL_MINUTES),
        ]);

        return $plain;
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
