<?php

declare(strict_types=1);

namespace App\Listeners\Commerce;

use App\Events\Commerce\OrderStatusChanged;
use App\Notifications\OrderStatusNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Notifies the customer (database/SMS/WhatsApp/push) when an order status
 * changes and the change is flagged to notify the customer.
 */
class NotifyOrderStatusChange implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(OrderStatusChanged $event): void
    {
        if (! $event->notifyCustomer) {
            return;
        }

        $user = $event->order->user;

        $user?->notify(new OrderStatusNotification($event->order, $event->status));
    }
}
