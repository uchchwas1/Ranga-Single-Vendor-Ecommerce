<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Application service for TOTP two-factor enrolment and verification.
 */
class TwoFactorService
{
    private const int RECOVERY_CODE_COUNT = 8;

    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly Google2FA $engine,
    ) {
    }

    /**
     * Begin two-factor enrolment: generate and store a pending secret.
     *
     * @return array{secret: string, otpauth_url: string, recovery_codes: list<string>}
     */
    public function enable(User $user): array
    {
        $secret = $this->engine->generateSecretKey();
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => null,
        ])->save();

        return [
            'secret' => $secret,
            'otpauth_url' => $this->engine->getQRCodeUrl(
                (string) config('app.name'),
                $user->email,
                $secret,
            ),
            'recovery_codes' => $recoveryCodes,
        ];
    }

    /**
     * Confirm enrolment with a valid TOTP code.
     */
    public function confirm(User $user, string $code): bool
    {
        if ($user->two_factor_secret === null || ! $this->verify($user, $code)) {
            return false;
        }

        $user->forceFill(['two_factor_confirmed_at' => Date::now()])->save();

        return true;
    }

    /**
     * Verify a TOTP code against the user's secret.
     */
    public function verify(User $user, string $code): bool
    {
        if ($user->two_factor_secret === null) {
            return false;
        }

        return $this->engine->verifyKey($user->two_factor_secret, $code) !== false;
    }

    /**
     * Redeem a recovery code; each code is single-use.
     */
    public function redeemRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        $index = array_search($code, $codes, true);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);

        $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

        return true;
    }

    /**
     * Disable two-factor authentication entirely.
     */
    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    /**
     * Generate a fresh set of single-use recovery codes.
     *
     * @return list<string>
     */
    private function generateRecoveryCodes(): array
    {
        return array_map(
            static fn (): string => Str::upper(Str::random(10)),
            range(1, self::RECOVERY_CODE_COUNT),
        );
    }
}
