<?php

declare(strict_types=1);

namespace App\Jobs\Auth;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Password;

/**
 * Queue job that generates a reset token and emails the reset link.
 */
class SendPasswordResetEmail implements ShouldQueue
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
        public readonly string $email,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /** @var \Illuminate\Auth\Passwords\PasswordBroker $broker */
        $broker = Password::broker();

        $user = $broker->getUser(['email' => $this->email]);

        if (! $user instanceof User) {
            return;
        }

        $user->sendPasswordResetNotification($broker->createToken($user));
    }
}
