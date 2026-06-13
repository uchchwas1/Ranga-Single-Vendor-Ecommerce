<?php

declare(strict_types=1);

namespace App\Events\Inventory;

use App\Models\Inventory;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Raised when an inventory row drops to or below its low-stock threshold.
 */
class InventoryLow
{
    use Dispatchable, SerializesModels;

    /**
     * @param  Inventory  $inventory  The inventory row that triggered the alert.
     */
    public function __construct(
        public readonly Inventory $inventory,
    ) {
    }
}
