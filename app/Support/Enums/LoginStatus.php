<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Status of a recorded login attempt.
 */
enum LoginStatus: string
{
    case Success = 'success';
    case Failed = 'failed';
    case Blocked = 'blocked';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Success => __('auth.login_status.success'),
            self::Failed => __('auth.login_status.failed'),
            self::Blocked => __('auth.login_status.blocked'),
        };
    }
}
