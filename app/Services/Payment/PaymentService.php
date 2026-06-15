<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Events\Commerce\OrderPaid;
use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentGatewayContract;
use App\Services\Payment\Data\PaymentInitiation;
use App\Services\Payment\Data\PaymentVerification;
use App\Services\Payment\Gateways\BkashGateway;
use App\Services\Payment\Gateways\CashOnDeliveryGateway;
use App\Services\Payment\Gateways\SslCommerzGateway;
use App\Services\Payment\Gateways\StripeGateway;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\PaymentGateway;
use App\Support\Enums\PaymentStatus;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Resolves payment gateway adapters (factory) and orchestrates the
 * payment lifecycle against Payment records.
 */
class PaymentService
{
    /**
     * Map of gateway codes to adapter classes.
     *
     * @var array<string, class-string<PaymentGatewayContract>>
     */
    private const GATEWAYS = [
        PaymentGateway::Cod->value => CashOnDeliveryGateway::class,
        PaymentGateway::Sslcommerz->value => SslCommerzGateway::class,
        PaymentGateway::Bkash->value => BkashGateway::class,
        PaymentGateway::Stripe->value => StripeGateway::class,
    ];

    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly Container $container,
    ) {
    }

    /**
     * Resolve the adapter for a gateway code (factory).
     *
     * @throws InvalidArgumentException when the gateway is unsupported.
     */
    public function gateway(PaymentGateway $gateway): PaymentGatewayContract
    {
        $class = self::GATEWAYS[$gateway->value] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException("Unsupported payment gateway [{$gateway->value}].");
        }

        /** @var PaymentGatewayContract $adapter */
        $adapter = $this->container->make($class);

        return $adapter;
    }

    /**
     * Begin payment for a pending Payment record.
     */
    public function initiate(Payment $payment): PaymentInitiation
    {
        $result = $this->gateway($payment->gateway)->initiate($payment);

        $payment->forceFill([
            'status' => $result->status,
            'gateway_transaction_id' => $result->transactionId ?? $payment->gateway_transaction_id,
            'payload' => $result->raw,
        ])->save();

        return $result;
    }

    /**
     * Verify a transaction and capture the payment on success.
     */
    public function verifyAndCapture(Payment $payment, string $transactionId): PaymentVerification
    {
        $verification = $this->gateway($payment->gateway)->verify($transactionId);

        $this->capture($payment, $verification, $transactionId);

        return $verification;
    }

    /**
     * Persist a verification result against the payment and its order.
     */
    public function capture(Payment $payment, PaymentVerification $verification, ?string $transactionId = null): void
    {
        $payment->forceFill([
            'status' => $verification->status,
            'gateway_transaction_id' => $verification->transactionId ?? $transactionId ?? $payment->gateway_transaction_id,
            'gateway_response' => $verification->raw,
            'paid_at' => $verification->successful ? now() : null,
        ])->save();

        $order = $payment->order;

        if ($order === null) {
            return;
        }

        if ($verification->successful) {
            $order->forceFill([
                'payment_status' => PaymentStatus::Paid,
                'status' => OrderStatus::Confirmed,
            ])->save();

            OrderPaid::dispatch($order);
        } elseif ($verification->status === PaymentStatus::Failed) {
            $order->forceFill(['payment_status' => PaymentStatus::Failed])->save();
        }
    }
}
