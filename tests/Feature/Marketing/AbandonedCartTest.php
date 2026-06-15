<?php

declare(strict_types=1);

namespace Tests\Feature\Marketing;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class AbandonedCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_command_flags_idle_carts_with_items(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1000]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'price_at_add' => 1000,
        ]);

        // Make the cart look idle beyond the abandonment threshold.
        Cart::query()->whereKey($cart->id)->update(['updated_at' => Date::now()->subHours(2)]);

        $this->assertSame(0, $this->artisan('ranga:mark-abandoned-carts'));

        $this->assertDatabaseHas('cart_abandonments', ['cart_id' => $cart->id, 'recovered' => false]);
    }

    public function test_fresh_carts_are_not_flagged(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->forUser($user)->create();
        $product = Product::factory()->create();
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => ProductVariant::factory()->for($product)->create()->id,
            'price_at_add' => 1000,
        ]);

        $this->assertSame(0, $this->artisan('ranga:mark-abandoned-carts'));

        $this->assertDatabaseMissing('cart_abandonments', ['cart_id' => $cart->id]);
    }
}
