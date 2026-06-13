<?php

declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoggedIn;
use App\Jobs\Auth\LogLoginActivity;
use App\Support\Enums\LoginStatus;

/**
 * Dispatches the audit job for a successful login.
 */
class RecordSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(UserLoggedIn $event): void
    {
        LogLoginActivity::dispatch($event->user->id, LoginStatus::Success, $event->ip, $event->userAgent);
    }
}
