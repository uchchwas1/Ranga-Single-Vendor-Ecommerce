<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\LoginFailed;
use App\Jobs\Auth\LogLoginActivity;
use App\Support\Enums\LoginStatus;

/**
 * Dispatches the audit job for a failed login attempt.
 */
class RecordFailedLogin
{
    /**
     * Handle the event.
     */
    public function handle(LoginFailed $event): void
    {
        LogLoginActivity::dispatch($event->userId, LoginStatus::Failed, $event->ip, $event->userAgent);
    }
}
