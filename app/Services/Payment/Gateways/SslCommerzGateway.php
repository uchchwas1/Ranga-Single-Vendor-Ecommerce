<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Models\Payment;
use App\Services\Payment\Data\PaymentInitiation;
use App\Services\Payment\Data\PaymentVerification;
use App\Support\Enums\PaymentGateway;
use App\Support\Enums\PaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * SSLCommerz — Bangladesh's primary aggregator (cards, bKash, Nagad, Rocket).
 */
class SslCommerzGateway extends AbstractGateway
{
    /**
     * The gateway this adapter handles.
     */
    public function code(): PaymentGateway
    {
        return PaymentGateway::Sslcommerz;
    }

    /**
     * Create a hosted payment session and return its redirect URL.
     */
    public function initiate(Payment $payment): PaymentInitiation
    {
        $config = $this->config();
        $order = $payment->order;

        $payload = [
            'store_id' => (string) ($config['store_id'] ?? ''),
            'store_passwd' => (string) ($config['store_password'] ?? ''),
            'total_amount' => (float) $payment->amount,
            'currency' => $payment->currency,
            'tran_id' => $payment->id,
            'success_url' => $this->callbackUrl(),
            'fail_url' => $this->callbackUrl(),
            'cancel_url' => $this->callbackUrl(),
            'cus_name' => $order?->user?->name ?? ($order?->guest_email ?? 'Guest'),
            'cus_email' => $order?->user?->email ?? ($order?->guest_email ?? 'guest@example.com'),
            'cus_phone' => '01700000000',
            'product_name' => 'Order '.($order?->order_number ?? $payment->order_id),
            'product_category' => 'general',
            'product_profile' => 'general',
            'shipping_method' => 'NO',
        ];

        $response = Http::asForm()->post($this->baseUrl().'/gwprocess/v4/api.php', $payload);
        $data = $response->json();
        $data = is_array($data) ? $data : [];

        $this->log($payment, 'initiate', $payload, $data);

        $url = $data['GatewayPageURL'] ?? null;

        if (($data['status'] ?? null) !== 'SUCCESS' || ! is_string($url) || $url === '') {
            return new PaymentInitiation(
                status: PaymentStatus::Failed,
                message: is_string($data['failedreason'] ?? null) ? $data['failedreason'] : __('commerce.payment.init_failed'),
                raw: $data,
            );
        }

        return PaymentInitiation::redirect($url, is_string($data['sessionkey'] ?? null) ? $data['sessionkey'] : null, $data);
    }

    /**
     * Validate a transaction via the SSLCommerz validation API.
     */
    public function verify(string $transactionId): PaymentVerification
    {
        $config = $this->config();

        $response = Http::get($this->baseUrl().'/validator/api/validationserverAPI.php', [
            'val_id' => $transactionId,
            'store_id' => (string) ($config['store_id'] ?? ''),
            'store_passwd' => (string) ($config['store_password'] ?? ''),
            'format' => 'json',
        ]);

        $data = $response->json();
        $data = is_array($data) ? $data : [];
        $status = $data['status'] ?? null;
        $success = in_array($status, ['VALID', 'VALIDATED'], true);

        return new PaymentVerification(
            successful: $success,
            status: $success ? PaymentStatus::Paid : PaymentStatus::Failed,
            transactionId: is_string($data['tran_id'] ?? null) ? $data['tran_id'] : $transactionId,
            raw: $data,
        );
    }

    /**
     * Issue a refund against a settled transaction.
     */
    public function refund(Payment $payment, float $amount): bool
    {
        $config = $this->config();

        $response = Http::get($this->baseUrl().'/validator/api/merchantTransIDvalidationAPI.php', [
            'bank_tran_id' => $payment->gateway_transaction_id,
            'store_id' => (string) ($config['store_id'] ?? ''),
            'store_passwd' => (string) ($config['store_password'] ?? ''),
            'refund_amount' => $amount,
            'format' => 'json',
        ]);

        $data = $response->json();
        $data = is_array($data) ? $data : [];
        $this->log($payment, 'refund', ['amount' => $amount], $data);

        return ($data['APIConnect'] ?? null) === 'DONE';
    }

    /**
     * Handle the SSLCommerz IPN/callback.
     */
    public function webhook(Request $request): PaymentVerification
    {
        $valId = $request->input('val_id');

        if (! is_string($valId) || $valId === '') {
            return new PaymentVerification(successful: false, status: PaymentStatus::Failed, raw: $request->all());
        }

        return $this->verify($valId);
    }

    /**
     * The API base URL for the configured environment.
     */
    private function baseUrl(): string
    {
        return ($this->config()['sandbox'] ?? true)
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }
}
