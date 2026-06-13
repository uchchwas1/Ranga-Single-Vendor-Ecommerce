<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $variant = ProductVariant::factory();

        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'variant_id' => $variant,
            'quantity' => fake()->numberBetween(1, 3),
            'price_at_add' => fake()->randomFloat(2, 500, 5000),
        ];
    }
}
