<?php

declare(strict_types=1);

namespace App\Events\Auth;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Raised when a password reset link has been requested.
 */
class PasswordResetRequested
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $email,
    ) {
    }
}
