<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartAbandonment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartAbandonment>
 */
class CartAbandonmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'user_id' => null,
            'email' => fake()->safeEmail(),
            'total' => fake()->randomFloat(2, 500, 5000),
            'recovered' => false,
        ];
    }
}
