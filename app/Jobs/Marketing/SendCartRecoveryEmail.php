<?php

declare(strict_types=1);

namespace App\Jobs\Marketing;

use App\Models\CartAbandonment;
use App\Services\Marketing\CartAbandonmentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Sends an abandoned-cart recovery email.
 *
 * The personalised copy (AI-generated) is layered in during the
 * notifications phase; this records intent and marks the send time.
 */
class SendCartRecoveryEmail implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * @param  string  $abandonmentId  ULID of the cart_abandonments row.
     */
    public function __construct(
        public readonly string $abandonmentId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(CartAbandonmentService $service): void
    {
        $abandonment = CartAbandonment::query()->find($this->abandonmentId);

        if ($abandonment === null || $abandonment->recovered || $abandonment->recovery_email_sent_at !== null) {
            return;
        }

        if ($abandonment->email === null) {
            return;
        }

        Log::info('Cart recovery email queued', [
            'abandonment_id' => $abandonment->id,
            'email' => $abandonment->email,
            'total' => $abandonment->total,
        ]);

        $service->markEmailSent($abandonment);
    }
}
