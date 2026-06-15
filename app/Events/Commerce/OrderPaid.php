<?php

declare(strict_types=1);

namespace App\Events\Commerce;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Raised when an order's payment is successfully captured.
 */
class OrderPaid
{
    use Dispatchable, SerializesModels;

    /**
     * @param  Order  $order  The paid order.
     */
    public function __construct(
        public readonly Order $order,
    ) {
    }
}
