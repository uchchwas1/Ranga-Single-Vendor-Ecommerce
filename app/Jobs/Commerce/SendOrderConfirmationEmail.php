<?php

declare(strict_types=1);

namespace App\Jobs\Commerce;

use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Sends the customer their order confirmation.
 *
 * The mailable is layered in during the Notifications phase; for now this
 * records the intent so the queue wiring is exercised end-to-end.
 */
class SendOrderConfirmationEmail implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The backoff (seconds) between retries.
     *
     * @var list<int>
     */
    public array $backoff = [30, 120, 300];

    /**
     * @param  string  $orderId  ULID of the placed order.
     */
    public function __construct(
        public readonly string $orderId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::query()->with('items')->find($this->orderId);

        if ($order === null) {
            return;
        }

        Log::info('Order confirmation queued', [
            'order_number' => $order->order_number,
            'recipient' => $order->user?->email ?? $order->guest_email,
            'total' => $order->total,
        ]);
    }
}
