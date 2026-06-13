<?php

declare(strict_types=1);

namespace App\Jobs\Auth;

use App\Models\LoginActivity;
use App\Support\Enums\LoginStatus;
use App\Support\UserAgentParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;

/**
 * Queue job that persists a login activity audit record.
 */
class LogLoginActivity implements ShouldQueue
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
        public readonly ?string $userId,
        public readonly LoginStatus $status,
        public readonly ?string $ip = null,
        public readonly ?string $userAgent = null,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $parsed = UserAgentParser::parse($this->userAgent);

        LoginActivity::query()->create([
            'user_id' => $this->userId,
            'ip' => $this->ip,
            'device' => $parsed['device'],
            'browser' => $parsed['browser'],
            'os' => $parsed['os'],
            'status' => $this->status,
            'created_at' => Date::now(),
        ]);
    }
}
