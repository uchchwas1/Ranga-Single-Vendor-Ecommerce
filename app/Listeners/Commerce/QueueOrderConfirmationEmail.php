<?php

declare(strict_types=1);

namespace App\Listeners\Commerce;

use App\Events\Commerce\OrderPlaced;
use App\Jobs\Commerce\SendOrderConfirmationEmail;

/**
 * Queues the order confirmation email when an order is placed.
 */
class QueueOrderConfirmationEmail
{
    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        SendOrderConfirmationEmail::dispatch($event->order->id);
    }
}
