<?php

declare(strict_types=1);

namespace App\Listeners\Commerce;

use App\Events\Commerce\OrderPlaced;
use App\Jobs\Commerce\UpdateInventoryOnOrder;

/**
 * Queues the stock decrement job when an order is placed.
 */
class QueueInventoryUpdate
{
    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        UpdateInventoryOnOrder::dispatch($event->order->id);
    }
}
