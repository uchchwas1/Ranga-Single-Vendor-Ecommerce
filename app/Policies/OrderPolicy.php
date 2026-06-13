<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

/**
 * Authorization rules for orders.
 */
class OrderPolicy
{
    /**
     * Whether the actor may view the given order.
     */
    public function view(User $actor, Order $order): bool
    {
        return $order->user_id === $actor->id || $actor->hasRole(['admin', 'super-admin']);
    }

    /**
     * Whether the actor may cancel the given order.
     */
    public function cancel(User $actor, Order $order): bool
    {
        return $order->user_id === $actor->id && $order->status->isCancellable();
    }
}
