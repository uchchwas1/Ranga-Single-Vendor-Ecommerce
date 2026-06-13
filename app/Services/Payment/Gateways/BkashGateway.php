<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Models\Payment;
use App\Services\Payment\Data\PaymentInitiation;
use App\Services\Payment\Data\PaymentVerification;
use App\Support\Enums\PaymentGateway;
use App\Support\Enums\PaymentStatus;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * bKash Tokenized Checkout (grant token → create → execute).
 */
class BkashGateway extends AbstractGateway
{
    /**
     * The gateway this adapter handles.
     */
    public function code(): PaymentGateway
    {
        return PaymentGateway::Bkash;
    }

    /**
     * Grant a token and create a payment, returning the bKash redirect URL.
     */
    public function initiate(Payment $payment): PaymentInitiation
    {
        $token = $this->grantToken();

        if ($token === null) {
            return new PaymentInitiation(status: PaymentStatus::Failed, message: __('commerce.payment.init_failed'));
        }

        $payload = [
            'mode' => '0011',
            'payerReference' => $payment->order_id,
            'callbackURL' => $this->callbackUrl(),
            'amount' => (string) $payment->amount,
            'currency' => $payment->currency,
            'intent' => 'sale',
            'merchantInvoiceNumber' => $payment->order?->order_number ?? $payment->id,
        ];

        $response = $this->client($token)->post($this->baseUrl().'/tokenized/checkout/create', $payload);
        $data = $response->json();
        $data = is_array($data) ? $data : [];

        $this->log($payment, 'create', $payload, $data);

        $url = $data['bkashURL'] ?? null;

        if (! is_string($url) || $url === '') {
            return new PaymentInitiation(status: PaymentStatus::Failed, message: __('commerce.payment.init_failed'), raw: $data);
        }

        return PaymentInitiation::redirect($url, is_string($data['paymentID'] ?? null) ? $data['paymentID'] : null, $data);
    }

    /**
     * Query a bKash payment's status.
     */
    public function verify(string $transactionId): PaymentVerification
    {
        $token = $this->grantToken();

        if ($token === null) {
            return new PaymentVerification(successful: false, status: PaymentStatus::Failed);
        }

        $response = $this->client($token)->post($this->baseUrl().'/tokenized/checkout/payment/status', [
            'paymentID' => $transactionId,
        ]);

        $data = $response->json();
        $data = is_array($data) ? $data : [];
        $success = ($data['transactionStatus'] ?? null) === 'Completed';

        return new PaymentVerification(
            successful: $success,
            status: $success ? PaymentStatus::Paid : PaymentStatus::Failed,
            transactionId: is_string($data['trxID'] ?? null) ? $data['trxID'] : $transactionId,
            raw: $data,
        );
    }

    /**
     * Refund a bKash transaction.
     */
    public function refund(Payment $payment, float $amount): bool
    {
        $token = $this->grantToken();

        if ($token === null) {
            return false;
        }

        $response = $this->client($token)->post($this->baseUrl().'/tokenized/checkout/payment/refund', [
            'paymentID' => $payment->gateway_transaction_id,
            'amount' => (string) $amount,
        ]);

        $data = $response->json();
        $data = is_array($data) ? $data : [];
        $this->log($payment, 'refund', ['amount' => $amount], $data);

        return ($data['transactionStatus'] ?? null) === 'Completed';
    }

    /**
     * Handle the bKash callback (?paymentID=&status=).
     */
    public function webhook(Request $request): PaymentVerification
    {
        $paymentId = $request->input('paymentID');

        if (! is_string($paymentId) || $request->input('status') !== 'success') {
            return new PaymentVerification(successful: false, status: PaymentStatus::Failed, raw: $request->all());
        }

        return $this->verify($paymentId);
    }

    /**
     * Obtain an id (grant) token, or null on failure.
     */
    private function grantToken(): ?string
    {
        $config = $this->config();

        $response = Http::withHeaders([
            'username' => (string) ($config['username'] ?? ''),
            'password' => (string) ($config['password'] ?? ''),
        ])->post($this->baseUrl().'/tokenized/checkout/token/grant', [
            'app_key' => (string) ($config['app_key'] ?? ''),
            'app_secret' => (string) ($config['app_secret'] ?? ''),
        ]);

        $token = $response->json('id_token');

        return is_string($token) && $token !== '' ? $token : null;
    }

    /**
     * An authorised HTTP client for checkout calls.
     */
    private function client(string $token): PendingRequest
    {
        $config = $this->config();

        return Http::withHeaders([
            'Authorization' => $token,
            'X-App-Key' => (string) ($config['app_key'] ?? ''),
        ]);
    }

    /**
     * The API base URL for the configured environment.
     */
    private function baseUrl(): string
    {
        return ($this->config()['sandbox'] ?? true)
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta';
    }
}
