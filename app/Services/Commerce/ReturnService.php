<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\RefundMethod;
use App\Support\Enums\ReturnStatus;
use Illuminate\Validation\ValidationException;

/**
 * Application service for customer return requests and admin decisions.
 */
class ReturnService
{
    /**
     * Submit a return request against a delivered/completed order.
     *
     * @param  array{order_item_id?: string|null, reason: string, description?: string|null, images?: array<int, string>|null}  $data
     *
     * @throws ValidationException
     */
    public function submit(Order $order, ?User $user, array $data): ReturnRequest
    {
        if (! in_array($order->status, [OrderStatus::Delivered, OrderStatus::Completed], true)) {
            throw ValidationException::withMessages(['order' => [__('commerce.return.not_returnable')]]);
        }

        /** @var ReturnRequest $request */
        $request = $order->returnRequests()->create([
            'order_item_id' => $data['order_item_id'] ?? null,
            'user_id' => $user?->id,
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
            'images' => $data['images'] ?? null,
            'status' => ReturnStatus::Pending,
        ]);

        return $request;
    }

    /**
     * Approve a pending return, recording the chosen refund method.
     *
     * @throws ValidationException
     */
    public function approve(ReturnRequest $return, RefundMethod $refundMethod, ?string $adminNote = null): ReturnRequest
    {
        $this->assertPending($return);

        $return->forceFill([
            'status' => ReturnStatus::Approved,
            'refund_method' => $refundMethod,
            'admin_note' => $adminNote,
        ])->save();

        return $return;
    }

    /**
     * Reject a pending return.
     *
     * @throws ValidationException
     */
    public function reject(ReturnRequest $return, ?string $adminNote = null): ReturnRequest
    {
        $this->assertPending($return);

        $return->forceFill([
            'status' => ReturnStatus::Rejected,
            'admin_note' => $adminNote,
        ])->save();

        return $return;
    }

    /**
     * Mark an approved return as completed (after the refund is issued).
     */
    public function complete(ReturnRequest $return): ReturnRequest
    {
        $return->forceFill(['status' => ReturnStatus::Completed])->save();

        return $return;
    }

    /**
     * @throws ValidationException
     */
    private function assertPending(ReturnRequest $return): void
    {
        if (! $return->status->isPending()) {
            throw ValidationException::withMessages(['return' => [__('commerce.return.not_pending')]]);
        }
    }
}
