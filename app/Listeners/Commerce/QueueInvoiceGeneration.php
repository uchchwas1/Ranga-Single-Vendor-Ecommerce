<?php

declare(strict_types=1);

namespace App\Listeners\Commerce;

use App\Events\Commerce\OrderPaid;
use App\Jobs\Commerce\GenerateInvoicePDF;

/**
 * Queues invoice generation when an order is paid.
 */
class QueueInvoiceGeneration
{
    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        GenerateInvoicePDF::dispatch($event->order->id);
    }
}
