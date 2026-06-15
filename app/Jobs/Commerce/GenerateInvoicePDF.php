<?php

declare(strict_types=1);

namespace App\Jobs\Commerce;

use App\Models\Order;
use App\Services\Invoice\InvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Generates and stores the invoice PDF once an order is paid.
 */
class GenerateInvoicePDF implements ShouldQueue
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
     * @param  string  $orderId  ULID of the paid order.
     */
    public function __construct(
        public readonly string $orderId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(InvoiceService $invoices): void
    {
        $order = Order::query()->with(['items', 'addresses'])->find($this->orderId);

        if ($order === null) {
            return;
        }

        $invoices->generate($order);
    }
}
