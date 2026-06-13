<?php

declare(strict_types=1);

namespace App\Events\Commerce;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Raised immediately after an order is successfully placed.
 */
class OrderPlaced
{
    use Dispatchable, SerializesModels;

    /**
     * @param  Order  $order  The newly created order.
     */
    public function __construct(
        public readonly Order $order,
    ) {
    }
}
