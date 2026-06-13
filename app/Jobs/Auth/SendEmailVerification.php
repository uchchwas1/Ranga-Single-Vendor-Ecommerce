<?php

declare(strict_types=1);

namespace App\Jobs\Auth;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Queue job that delivers the email verification notification.
 */
class SendEmailVerification implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The backoff schedule, in seconds, between retries.
     *
     * @var list<int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly User $user,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->user->hasVerifiedEmail()) {
            return;
        }

        $this->user->sendEmailVerificationNotification();
    }
}
