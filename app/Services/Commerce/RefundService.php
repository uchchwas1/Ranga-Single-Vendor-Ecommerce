<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\Payment;
use App\Models\Refund;
use App\Models\ReturnRequest;
use App\Services\Payment\PaymentService;
use App\Support\Enums\PaymentStatus;
use App\Support\Enums\RefundMethod;
use App\Support\Enums\RefundStatus;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Application service that issues refunds, calling the originating
 * gateway adapter when the refund method is "original payment".
 */
class RefundService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly PaymentService $payments,
        private readonly ReturnService $returns,
    ) {
    }

    /**
     * Process a refund for an approved return request.
     *
     * @throws ValidationException
     */
    public function processForReturn(ReturnRequest $return, ?float $amount = null): Refund
    {
        $order = $return->order;
        $amount ??= (float) $order->total;
        $method = $return->refund_method ?? RefundMethod::OriginalPayment;

        /** @var Payment|null $payment */
        $payment = $order->payments()->where('status', PaymentStatus::Paid->value)->first();

        return DB::transaction(function () use ($return, $order, $payment, $amount, $method): Refund {
            /** @var Refund $refund */
            $refund = $return->refunds()->create([
                'order_id' => $order->id,
                'payment_id' => $payment?->id,
                'amount' => $amount,
                'status' => RefundStatus::Processing,
            ]);

            $succeeded = true;

            if ($method->usesGateway() && $payment !== null) {
                $succeeded = $this->payments->gateway($payment->gateway)->refund($payment, $amount);
            }

            $refund->forceFill([
                'status' => $succeeded ? RefundStatus::Completed : RefundStatus::Failed,
                'processed_at' => $succeeded ? Date::now() : null,
            ])->save();

            if ($succeeded) {
                if ($payment !== null) {
                    $payment->forceFill(['status' => PaymentStatus::Refunded])->save();
                }

                $order->forceFill(['payment_status' => PaymentStatus::Refunded])->save();
                $this->returns->complete($return);
            }

            return $refund;
        });
    }
}
