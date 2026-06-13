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
 * Stripe Checkout Sessions for international card payments.
 */
class StripeGateway extends AbstractGateway
{
    private const string API_BASE = 'https://api.stripe.com/v1';

    /**
     * The gateway this adapter handles.
     */
    public function code(): PaymentGateway
    {
        return PaymentGateway::Stripe;
    }

    /**
     * Create a Checkout Session and return its hosted URL.
     */
    public function initiate(Payment $payment): PaymentInitiation
    {
        // Stripe expects the smallest currency unit (e.g. paisa/cents).
        $minorAmount = (int) round((float) $payment->amount * 100);

        $payload = [
            'mode' => 'payment',
            'success_url' => $this->callbackUrl().'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->callbackUrl(),
            'client_reference_id' => $payment->id,
            'line_items[0][quantity]' => 1,
            'line_items[0][price_data][currency]' => mb_strtolower($payment->currency),
            'line_items[0][price_data][unit_amount]' => $minorAmount,
            'line_items[0][price_data][product_data][name]' => 'Order '.($payment->order?->order_number ?? $payment->order_id),
        ];

        $response = $this->client()->asForm()->post(self::API_BASE.'/checkout/sessions', $payload);
        $data = $response->json();
        $data = is_array($data) ? $data : [];

        $this->log($payment, 'create_session', $payload, $data);

        $url = $data['url'] ?? null;

        if (! is_string($url) || $url === '') {
            return new PaymentInitiation(status: PaymentStatus::Failed, message: __('commerce.payment.init_failed'), raw: $data);
        }

        return PaymentInitiation::redirect($url, is_string($data['id'] ?? null) ? $data['id'] : null, $data);
    }

    /**
     * Retrieve a Checkout Session and map its payment status.
     */
    public function verify(string $transactionId): PaymentVerification
    {
        $response = $this->client()->get(self::API_BASE.'/checkout/sessions/'.$transactionId);
        $data = $response->json();
        $data = is_array($data) ? $data : [];
        $success = ($data['payment_status'] ?? null) === 'paid';

        return new PaymentVerification(
            successful: $success,
            status: $success ? PaymentStatus::Paid : PaymentStatus::Failed,
            transactionId: $transactionId,
            raw: $data,
        );
    }

    /**
     * Refund a payment intent associated with the session.
     */
    public function refund(Payment $payment, float $amount): bool
    {
        $response = $this->client()->asForm()->post(self::API_BASE.'/refunds', [
            'payment_intent' => $payment->gateway_transaction_id,
            'amount' => (int) round($amount * 100),
        ]);

        $data = $response->json();
        $data = is_array($data) ? $data : [];
        $this->log($payment, 'refund', ['amount' => $amount], $data);

        return in_array($data['status'] ?? null, ['succeeded', 'pending'], true);
    }

    /**
     * Handle the Stripe redirect callback (?session_id=).
     */
    public function webhook(Request $request): PaymentVerification
    {
        $sessionId = $request->input('session_id');

        if (! is_string($sessionId) || $sessionId === '') {
            return new PaymentVerification(successful: false, status: PaymentStatus::Failed, raw: $request->all());
        }

        return $this->verify($sessionId);
    }

    /**
     * An HTTP client authenticated with the Stripe secret key.
     */
    private function client(): PendingRequest
    {
        return Http::withToken((string) ($this->config()['secret'] ?? ''));
    }
}
