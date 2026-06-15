<?php

declare(strict_types=1);

namespace Tests\Feature\Marketing;

use App\Models\Affiliate;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AffiliateReferralTest extends TestCase
{
    use RefreshDatabase;

    private function seedPurchasable(): ProductVariant
    {
        $product = Product::factory()->create(['weight' => 0.5]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 10]);
        $warehouse = Warehouse::factory()->create();
        Inventory::factory()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]);
        ShippingMethod::factory()->withRate(60)->create(['code' => 'standard', 'min_order_amount' => 0]);

        return $variant;
    }

    private function addToCart(ProductVariant $variant): void
    {
        $this->postJson('/api/v1/cart/items', [
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertCreated();
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function payload(array $extra = []): array
    {
        return array_merge([
            'shipping' => ['name' => 'A', 'phone' => '01712345678', 'address_line_1' => 'X', 'city' => 'Dhaka'],
            'shipping_method' => 'standard',
            'payment_gateway' => 'cod',
        ], $extra);
    }

    public function test_an_affiliate_click_is_recorded(): void
    {
        $affiliate = Affiliate::factory()->create(['code' => 'AFF123']);

        $this->getJson('/api/v1/aff/AFF123')
            ->assertOk()
            ->assertJsonPath('affiliate_code', 'AFF123');

        $this->assertDatabaseHas('affiliate_clicks', ['affiliate_id' => $affiliate->id]);
    }

    public function test_an_unknown_affiliate_code_is_not_found(): void
    {
        $this->getJson('/api/v1/aff/NOPE')->assertNotFound();
    }

    public function test_a_checkout_with_an_affiliate_code_records_a_conversion(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $affiliate = Affiliate::factory()->create(['code' => 'AFF777', 'commission_rate' => 10]);
        $variant = $this->seedPurchasable();
        $this->addToCart($variant);

        $this->postJson('/api/v1/checkout', $this->payload(['affiliate_code' => 'AFF777']))->assertCreated();

        // total 1060, 10% commission = 106.
        $this->assertDatabaseHas('affiliate_conversions', ['affiliate_id' => $affiliate->id, 'commission' => 106.00]);
    }

    public function test_a_referred_users_order_grants_the_referrer_a_reward(): void
    {
        $referrer = User::factory()->create(['loyalty_points' => 0]);
        $referred = User::factory()->create(['referred_by' => $referrer->id]);
        Sanctum::actingAs($referred);
        $variant = $this->seedPurchasable();
        $this->addToCart($variant);

        $this->postJson('/api/v1/checkout', $this->payload())->assertCreated();

        $this->assertDatabaseHas('referral_rewards', [
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
        ]);
        // Referrer credited 100 points (default referral reward).
        $this->assertGreaterThanOrEqual(100, $referrer->fresh()?->loyalty_points ?? 0);
    }
}
