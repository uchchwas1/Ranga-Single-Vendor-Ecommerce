<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed a product with stock, inventory and a shipping method, then
     * return the variant for cart operations.
     */
    private function seedPurchasable(int $stock = 10): ProductVariant
    {
        $product = Product::factory()->create(['weight' => 0.5]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => $stock]);
        $warehouse = Warehouse::factory()->create();

        Inventory::factory()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $stock,
            'low_stock_threshold' => 2,
        ]);

        ShippingMethod::factory()->withRate(60)->create([
            'code' => 'standard',
            'min_order_amount' => 0,
            'is_active' => true,
        ]);

        return $variant;
    }

    private function addToCart(ProductVariant $variant, int $qty = 2): void
    {
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'quantity' => $qty,
        ])->assertCreated();
    }

    /**
     * @return array<string, mixed>
     */
    private function checkoutPayload(string $gateway): array
    {
        return [
            'shipping' => [
                'name' => 'Anika Rahman',
                'phone' => '01712345678',
                'address_line_1' => '12 Gulshan Ave',
                'city' => 'Dhaka',
            ],
            'shipping_method' => 'standard',
            'payment_gateway' => $gateway,
        ];
    }

    public function test_it_lists_available_shipping_methods(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = $this->seedPurchasable();
        $this->addToCart($variant);

        $this->getJson('/api/v1/checkout/shipping-methods')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'standard')
            ->assertJsonPath('data.0.cost', 60);
    }

    public function test_cod_checkout_places_an_order_clears_cart_and_decrements_stock(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = $this->seedPurchasable(10);
        $this->addToCart($variant, 2);

        $response = $this->postJson('/api/v1/checkout', $this->checkoutPayload('cod'));

        $response->assertCreated()
            ->assertJsonPath('payment.requires_redirect', false)
            ->assertJsonPath('payment.gateway', 'cod')
            ->assertJsonPath('data.payment_status', 'pending');

        // Subtotal 2 × 1000 + 60 shipping.
        $this->assertEquals(2060, (float) $response->json('data.total'));

        $this->assertDatabaseHas('orders', ['order_number' => $response->json('data.order_number')]);
        $this->getJson('/api/v1/cart')->assertOk()->assertJsonPath('data.item_count', 0);
        $this->assertEquals(8, Inventory::query()->where('variant_id', $variant->id)->value('quantity'));
    }

    public function test_guest_checkout_requires_an_email(): void
    {
        $variant = $this->seedPurchasable();
        $token = $this->getJson('/api/v1/cart')->json('data.cart_token');
        $headers = ['X-Cart-Token' => $token];

        $this->withHeaders($headers)->postJson('/api/v1/cart/items', [
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertCreated();

        $this->withHeaders($headers)
            ->postJson('/api/v1/checkout', $this->checkoutPayload('cod'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['guest_email']);
    }

    public function test_online_checkout_returns_a_redirect_url(): void
    {
        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/pay/abc123',
                'sessionkey' => 'sess_123',
            ]),
        ]);

        Sanctum::actingAs(User::factory()->create());
        $variant = $this->seedPurchasable();
        $this->addToCart($variant, 1);

        $this->postJson('/api/v1/checkout', $this->checkoutPayload('sslcommerz'))
            ->assertCreated()
            ->assertJsonPath('payment.requires_redirect', true)
            ->assertJsonPath('payment.redirect_url', 'https://sandbox.sslcommerz.com/pay/abc123');
    }

    public function test_gateway_callback_marks_the_order_paid(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'gwprocess')) {
                return Http::response([
                    'status' => 'SUCCESS',
                    'GatewayPageURL' => 'https://sandbox.sslcommerz.com/pay/abc123',
                    'sessionkey' => 'sess_123',
                ]);
            }

            return Http::response(['status' => 'VALID', 'tran_id' => 'bank_txn_1']);
        });

        Sanctum::actingAs(User::factory()->create());
        $variant = $this->seedPurchasable();
        $this->addToCart($variant, 1);

        $number = $this->postJson('/api/v1/checkout', $this->checkoutPayload('sslcommerz'))
            ->assertCreated()
            ->json('data.order_number');

        $payment = \App\Models\Order::query()->where('order_number', $number)->firstOrFail()->payments()->firstOrFail();

        $this->postJson("/api/v1/checkout/payment/sslcommerz/callback?tran_id={$payment->id}&val_id=val_abc")
            ->assertOk()
            ->assertJsonPath('successful', true)
            ->assertJsonPath('payment_status', 'paid');

        $this->assertSame('paid', $payment->fresh()?->status->value);
        $this->assertSame('confirmed', \App\Models\Order::query()->where('order_number', $number)->firstOrFail()->status->value);
    }
}
