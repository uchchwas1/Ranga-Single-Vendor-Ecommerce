<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Events\Commerce\OrderStatusChanged;
use App\Models\Order;
use App\Support\Enums\OrderStatus;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Application service for order status management and cancellation.
 */
class OrderManagementService
{
    /**
     * Transition an order to a new status, recording the timeline entry
     * and dispatching the status-changed event for downstream notifications.
     */
    public function changeStatus(
        Order $order,
        OrderStatus $status,
        ?string $comment = null,
        bool $notifyCustomer = false,
        ?string $actorId = null,
    ): Order {
        return DB::transaction(function () use ($order, $status, $comment, $notifyCustomer, $actorId): Order {
            $order->forceFill(['status' => $status])->save();

            $order->statusHistories()->create([
                'status' => $status,
                'comment' => $comment,
                'notify_customer' => $notifyCustomer,
                'created_by' => $actorId,
            ]);

            OrderStatusChanged::dispatch($order, $status, $comment, $notifyCustomer);

            return $order;
        });
    }

    /**
     * Cancel an order if it is still in a cancellable state.
     *
     * @throws ValidationException
     */
    public function cancel(Order $order, ?string $reason, ?string $actorId = null): Order
    {
        if (! $order->status->isCancellable()) {
            throw ValidationException::withMessages(['order' => [__('commerce.order.not_cancellable')]]);
        }

        $order->forceFill([
            'cancel_reason' => $reason,
            'cancelled_at' => Date::now(),
        ])->save();

        return $this->changeStatus($order, OrderStatus::Cancelled, $reason, true, $actorId);
    }
}
