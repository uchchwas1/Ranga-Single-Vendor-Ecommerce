<?php

declare(strict_types=1);

namespace Tests\Feature\Marketing;

use App\Models\GiftCard;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GiftCardLoyaltyTest extends TestCase
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

    private function addToCart(ProductVariant $variant, int $qty = 1): void
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
    private function payload(array $extra = []): array
    {
        return array_merge([
            'shipping' => ['name' => 'A', 'phone' => '01712345678', 'address_line_1' => 'X', 'city' => 'Dhaka'],
            'shipping_method' => 'standard',
            'payment_gateway' => 'cod',
        ], $extra);
    }

    public function test_a_gift_card_is_deducted_at_checkout(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $variant = $this->seedPurchasable();
        $this->addToCart($variant, 1);
        $card = GiftCard::factory()->create(['code' => 'GCTEST', 'initial_balance' => 500, 'current_balance' => 500]);

        // 1000 + 60 shipping = 1060; gift card covers 500 -> total 560.
        $response = $this->postJson('/api/v1/checkout', $this->payload(['gift_card_code' => 'GCTEST']));

        $response->assertCreated();
        $this->assertEquals(560, (float) $response->json('data.total'));
        $this->assertEquals(0, (float) $card->fresh()?->current_balance);
    }

    public function test_loyalty_points_are_earned_when_an_order_is_placed(): void
    {
        $user = User::factory()->create(['loyalty_points' => 0]);
        Sanctum::actingAs($user);
        $variant = $this->seedPurchasable();
        $this->addToCart($variant, 1);

        $this->postJson('/api/v1/checkout', $this->payload())->assertCreated();

        // total 1060 -> floor(1060/100) = 10 points.
        $this->assertSame(10, $user->fresh()?->loyalty_points);
        $this->assertDatabaseHas('loyalty_transactions', ['user_id' => $user->id, 'type' => 'earn']);
    }

    public function test_loyalty_points_can_be_redeemed_at_checkout(): void
    {
        $user = User::factory()->create(['loyalty_points' => 300]);
        Sanctum::actingAs($user);
        $variant = $this->seedPurchasable();
        $this->addToCart($variant, 1);

        // Redeem 100 points (= ৳100): 1060 - 100 = 960.
        $response = $this->postJson('/api/v1/checkout', $this->payload(['redeem_points' => 100]));

        $response->assertCreated();
        $this->assertEquals(960, (float) $response->json('data.total'));
        $this->assertDatabaseHas('loyalty_transactions', ['user_id' => $user->id, 'type' => 'redeem']);
    }

    public function test_profile_loyalty_endpoint_returns_balance_and_tier(): void
    {
        $user = User::factory()->create(['loyalty_points' => 600]);
        Sanctum::actingAs($user);
        \App\Models\LoyaltyTier::factory()->create(['name' => 'Silver', 'min_points' => 500, 'discount_percent' => 5]);

        $this->getJson('/api/v1/profile/loyalty')
            ->assertOk()
            ->assertJsonPath('balance', 600)
            ->assertJsonPath('tier.name', 'Silver');
    }
}
