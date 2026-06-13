<?php

declare(strict_types=1);

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Queued password reset notification.
 */
class ResetPasswordNotification extends ResetPassword implements ShouldQueue
{
    use Queueable;
}
