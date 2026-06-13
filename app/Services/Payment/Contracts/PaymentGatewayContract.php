<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

use App\Models\Payment;
use App\Services\Payment\Data\PaymentInitiation;
use App\Services\Payment\Data\PaymentVerification;
use App\Support\Enums\PaymentGateway;
use Illuminate\Http\Request;

/**
 * Common contract every payment gateway adapter must implement.
 */
interface PaymentGatewayContract
{
    /**
     * The gateway this adapter handles.
     */
    public function code(): PaymentGateway;

    /**
     * Begin a payment for the given pending payment record.
     */
    public function initiate(Payment $payment): PaymentInitiation;

    /**
     * Verify the status of a transaction with the gateway.
     */
    public function verify(string $transactionId): PaymentVerification;

    /**
     * Refund (part of) a captured payment.
     */
    public function refund(Payment $payment, float $amount): bool;

    /**
     * Handle an inbound gateway webhook / callback.
     */
    public function webhook(Request $request): PaymentVerification;
}
