<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductTag>
 */
class ProductTagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'tag' => fake()->unique()->word(),
        ];
    }
}
