<?php

declare(strict_types=1);

namespace App\Listeners\Inventory;

use App\Events\Inventory\InventoryLow;
use App\Jobs\Inventory\SendLowStockAlert;

/**
 * Queues a low-stock alert job in response to the InventoryLow event.
 */
class QueueLowStockAlert
{
    /**
     * Handle the event.
     */
    public function handle(InventoryLow $event): void
    {
        SendLowStockAlert::dispatch($event->inventory->id);
    }
}
