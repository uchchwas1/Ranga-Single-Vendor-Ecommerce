<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\PasswordResetRequested;
use App\Jobs\Auth\SendPasswordResetEmail;

/**
 * Dispatches the password reset email job.
 */
class QueuePasswordResetEmail
{
    /**
     * Handle the event.
     */
    public function handle(PasswordResetRequested $event): void
    {
        SendPasswordResetEmail::dispatch($event->email);
    }
}
