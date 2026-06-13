<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\UserRegistered;
use App\Jobs\Auth\SendEmailVerification;

/**
 * Dispatches the email verification job when a user registers.
 */
class QueueEmailVerification
{
    /**
     * Handle the event.
     */
    public function handle(UserRegistered $event): void
    {
        SendEmailVerification::dispatch($event->user);
    }
}
