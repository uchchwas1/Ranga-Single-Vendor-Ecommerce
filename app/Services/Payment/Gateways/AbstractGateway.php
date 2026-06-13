<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Models\Payment;
use App\Models\PaymentGatewayLog;
use App\Services\Payment\Contracts\PaymentGatewayContract;
use App\Support\Enums\PaymentGateway;

/**
 * Shared behaviour for concrete gateway adapters.
 */
abstract class AbstractGateway implements PaymentGatewayContract
{
    /**
     * Persist a gateway interaction for audit/debugging.
     *
     * @param  array<string, mixed>  $request
     * @param  array<string, mixed>  $response
     */
    protected function log(Payment $payment, string $event, array $request = [], array $response = []): void
    {
        PaymentGatewayLog::query()->create([
            'payment_id' => $payment->id,
            'event' => $event,
            'request' => $this->redact($request),
            'response' => $response,
        ]);
    }

    /**
     * Build the gateway callback URL for this adapter.
     */
    protected function callbackUrl(): string
    {
        return route('api.v1.checkout.callback', ['gateway' => $this->code()->value]);
    }

    /**
     * Gateway-specific configuration block from config/services.php.
     *
     * @return array<string, mixed>
     */
    protected function config(): array
    {
        $config = config('services.'.$this->code()->value);

        return is_array($config) ? $config : [];
    }

    /**
     * Remove obviously sensitive keys before persisting a request log.
     *
     * @param  array<string, mixed>  $request
     * @return array<string, mixed>
     */
    private function redact(array $request): array
    {
        foreach (['store_passwd', 'password', 'app_secret', 'secret', 'signature_key'] as $key) {
            if (array_key_exists($key, $request)) {
                $request[$key] = '***';
            }
        }

        return $request;
    }

    /**
     * The gateway this adapter handles.
     */
    abstract public function code(): PaymentGateway;
}
