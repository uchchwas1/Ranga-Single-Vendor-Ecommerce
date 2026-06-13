<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductImage>
 */
class ProductImageFactory extends Factory
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
            'image_path' => 'products/'.fake()->uuid().'.webp',
            'alt_text' => fake()->sentence(3),
            'sort_order' => fake()->numberBetween(0, 10),
            'is_primary' => false,
        ];
    }

    /**
     * The primary gallery image.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_primary' => true,
            'sort_order' => 0,
        ]);
    }
}
