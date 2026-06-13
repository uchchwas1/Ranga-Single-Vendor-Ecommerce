<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Models\Payment;
use App\Services\Payment\Data\PaymentInitiation;
use App\Services\Payment\Data\PaymentVerification;
use App\Support\Enums\PaymentGateway;
use App\Support\Enums\PaymentStatus;
use Illuminate\Http\Request;

/**
 * Cash on Delivery — no gateway call; payment is confirmed on delivery.
 */
class CashOnDeliveryGateway extends AbstractGateway
{
    /**
     * The gateway this adapter handles.
     */
    public function code(): PaymentGateway
    {
        return PaymentGateway::Cod;
    }

    /**
     * COD requires no redirect; the order is accepted as pending payment.
     */
    public function initiate(Payment $payment): PaymentInitiation
    {
        $this->log($payment, 'initiate', ['order_id' => $payment->order_id]);

        return PaymentInitiation::confirmed(__('commerce.payment.cod_placed'));
    }

    /**
     * COD has no remote state; it is settled manually on delivery.
     */
    public function verify(string $transactionId): PaymentVerification
    {
        return new PaymentVerification(successful: false, status: PaymentStatus::Pending);
    }

    /**
     * COD refunds are handled manually.
     */
    public function refund(Payment $payment, float $amount): bool
    {
        $this->log($payment, 'refund', ['amount' => $amount]);

        return true;
    }

    /**
     * COD has no webhook.
     */
    public function webhook(Request $request): PaymentVerification
    {
        return new PaymentVerification(successful: false, status: PaymentStatus::Pending);
    }
}
