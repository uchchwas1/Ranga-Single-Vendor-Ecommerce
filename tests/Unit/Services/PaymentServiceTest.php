<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\Gateways\BkashGateway;
use App\Services\Payment\Gateways\CashOnDeliveryGateway;
use App\Services\Payment\Gateways\SslCommerzGateway;
use App\Services\Payment\Gateways\StripeGateway;
use App\Services\Payment\PaymentService;
use App\Support\Enums\PaymentGateway;
use App\Support\Enums\PaymentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): PaymentService
    {
        return app(PaymentService::class);
    }

    public function test_the_factory_resolves_implemented_adapters(): void
    {
        $this->assertInstanceOf(CashOnDeliveryGateway::class, $this->service()->gateway(PaymentGateway::Cod));
        $this->assertInstanceOf(SslCommerzGateway::class, $this->service()->gateway(PaymentGateway::Sslcommerz));
        $this->assertInstanceOf(BkashGateway::class, $this->service()->gateway(PaymentGateway::Bkash));
        $this->assertInstanceOf(StripeGateway::class, $this->service()->gateway(PaymentGateway::Stripe));
    }

    public function test_it_throws_for_an_unsupported_gateway(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service()->gateway(PaymentGateway::Nagad);
    }

    public function test_cod_initiation_keeps_the_payment_pending_without_redirect(): void
    {
        $order = Order::factory()->create();
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'gateway' => PaymentGateway::Cod,
            'status' => PaymentStatus::Pending,
        ]);

        $initiation = $this->service()->initiate($payment);

        $this->assertFalse($initiation->requiresRedirect);
        $this->assertSame(PaymentStatus::Pending, $initiation->status);
        $this->assertSame(PaymentStatus::Pending, $payment->fresh()?->status);
    }
}
