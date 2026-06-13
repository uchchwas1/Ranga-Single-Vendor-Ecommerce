<?php

declare(strict_types=1);

namespace App\Events\Auth;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Raised when a login attempt fails credential verification.
 */
class LoginFailed
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly ?string $userId,
        public readonly ?string $ip = null,
        public readonly ?string $userAgent = null,
    ) {
    }
}
