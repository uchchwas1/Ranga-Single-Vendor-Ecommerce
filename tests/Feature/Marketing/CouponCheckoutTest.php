<?php

declare(strict_types=1);

namespace Tests\Feature\Marketing;

use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CouponCheckoutTest extends TestCase
{
    use RefreshDatabase;

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
        ]);
        ShippingMethod::factory()->withRate(60)->create(['code' => 'standard', 'min_order_amount' => 0]);

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
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function checkoutPayload(array $extra = []): array
    {
        return array_merge([
            'shipping' => [
                'name' => 'Anika',
                'phone' => '01712345678',
                'address_line_1' => '12 Rd',
                'city' => 'Dhaka',
            ],
            'shipping_method' => 'standard',
            'payment_gateway' => 'cod',
        ], $extra);
    }

    public function test_a_percentage_coupon_reduces_the_order_total(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = $this->seedPurchasable();
        $this->addToCart($variant, 2);
        Coupon::factory()->create(['code' => 'SAVE10', 'value' => 10]); // 10% percent

        $response = $this->postJson('/api/v1/checkout', $this->checkoutPayload(['coupon_code' => 'SAVE10']));

        $response->assertCreated();
        // 2000 subtotal - 200 (10%) + 60 shipping
        $this->assertEquals(200, (float) $response->json('data.discount_amount'));
        $this->assertEquals(1860, (float) $response->json('data.total'));
        $this->assertDatabaseHas('coupon_usages', ['discount_amount' => 200.00]);
    }

    public function test_a_free_shipping_coupon_waives_the_shipping_fee(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = $this->seedPurchasable();
        $this->addToCart($variant, 1);
        Coupon::factory()->freeShipping()->create(['code' => 'FREESHIP']);

        $response = $this->postJson('/api/v1/checkout', $this->checkoutPayload(['coupon_code' => 'FREESHIP']));

        $response->assertCreated();
        $this->assertEquals(0, (float) $response->json('data.shipping_amount'));
        $this->assertEquals(1000, (float) $response->json('data.total'));
    }

    public function test_a_coupon_applied_to_the_cart_carries_into_checkout(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = $this->seedPurchasable();
        $this->addToCart($variant, 1);
        Coupon::factory()->fixed(150)->create(['code' => 'FLAT150']);

        $this->postJson('/api/v1/cart/coupon', ['code' => 'FLAT150'])
            ->assertOk()
            ->assertJsonPath('data.coupon_code', 'FLAT150');

        $response = $this->postJson('/api/v1/checkout', $this->checkoutPayload());

        $response->assertCreated();
        $this->assertEquals(150, (float) $response->json('data.discount_amount'));
    }

    public function test_an_expired_coupon_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = $this->seedPurchasable();
        $this->addToCart($variant, 1);
        Coupon::factory()->expired()->create(['code' => 'OLD']);

        $this->postJson('/api/v1/checkout', $this->checkoutPayload(['coupon_code' => 'OLD']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['coupon']);
    }

    public function test_a_coupon_below_minimum_order_is_rejected(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = $this->seedPurchasable();
        $this->addToCart($variant, 1);
        Coupon::factory()->create(['code' => 'BIG', 'min_order_amount' => 5000]);

        $this->postJson('/api/v1/cart/coupon', ['code' => 'BIG'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['coupon']);
    }
}
