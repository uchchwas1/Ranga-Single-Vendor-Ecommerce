<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\SavedCartItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedCartItem>
 */
class SavedCartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'quantity' => fake()->numberBetween(1, 3),
        ];
    }
}
