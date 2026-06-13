<?php

declare(strict_types=1);

namespace App\Support\Dto;

use App\Models\User;

/**
 * Result of an authentication attempt: either an issued token
 * or a pending two-factor challenge.
 */
final readonly class AuthResult
{
    private function __construct(
        public ?User $user,
        public ?string $token,
        public ?string $challengeToken,
    ) {
    }

    /**
     * A fully authenticated result carrying a Sanctum token.
     */
    public static function authenticated(User $user, string $token): self
    {
        return new self($user, $token, null);
    }

    /**
     * A partial result requiring two-factor verification.
     */
    public static function twoFactorRequired(string $challengeToken): self
    {
        return new self(null, null, $challengeToken);
    }

    /**
     * Whether two-factor verification is still required.
     */
    public function requiresTwoFactor(): bool
    {
        return $this->challengeToken !== null;
    }
}
