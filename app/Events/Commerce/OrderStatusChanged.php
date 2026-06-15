<?php

declare(strict_types=1);

namespace App\Events\Commerce;

use App\Models\Order;
use App\Support\Enums\OrderStatus;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Raised whenever an order transitions to a new status. Notification
 * side effects (SMS/WhatsApp) are layered on in the notifications phase.
 */
class OrderStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly OrderStatus $status,
        public readonly ?string $comment = null,
        public readonly bool $notifyCustomer = false,
    ) {
    }
}
