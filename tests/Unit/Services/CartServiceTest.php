<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Commerce\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): CartService
    {
        return app(CartService::class);
    }

    public function test_it_resolves_a_user_scoped_cart(): void
    {
        $user = User::factory()->create();

        $cart = $this->service()->resolve($user, null);

        $this->assertSame($user->id, $cart->user_id);
        $this->assertNull($cart->session_id);
    }

    public function test_it_adds_and_increments_a_line(): void
    {
        $user = User::factory()->create();
        $cart = $this->service()->resolve($user, null);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1200, 'stock' => 10]);

        $this->service()->addItem($cart, $product->id, $variant->id, 1);
        $this->service()->addItem($cart, $product->id, $variant->id, 2);

        $this->assertSame(1, $cart->items()->count());
        $this->assertSame(3, $cart->items()->first()?->quantity);
    }

    public function test_it_rejects_quantities_beyond_stock(): void
    {
        $user = User::factory()->create();
        $cart = $this->service()->resolve($user, null);
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1200, 'stock' => 2]);

        $this->expectException(ValidationException::class);

        $this->service()->addItem($cart, $product->id, $variant->id, 5);
    }
}
