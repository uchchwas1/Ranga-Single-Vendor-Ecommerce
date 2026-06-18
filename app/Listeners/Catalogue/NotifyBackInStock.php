<?php

declare(strict_types=1);

namespace App\Listeners\Catalogue;

use App\Events\Catalogue\VariantRestocked;
use App\Services\Commerce\BackInStockService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Sends back-in-stock notifications when a variant is restocked.
 */
class NotifyBackInStock implements ShouldQueue
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        private readonly BackInStockService $backInStock,
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(VariantRestocked $event): void
    {
        $this->backInStock->notifyRestocked($event->variant);
    }
}
