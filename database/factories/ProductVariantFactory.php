<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 500, 5000);

        return [
            'product_id' => Product::factory(),
            'sku' => 'VAR-'.Str::upper(Str::random(8)),
            'barcode' => (string) fake()->ean13(),
            'price' => $price,
            'compare_price' => $price + fake()->randomFloat(2, 100, 1000),
            'cost_price' => $price - fake()->randomFloat(2, 50, 400),
            'stock' => fake()->numberBetween(0, 100),
            'weight' => fake()->randomFloat(3, 0.1, 2),
            'image_id' => null,
            'is_active' => true,
        ];
    }

    /**
     * An inactive variant.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }

    /**
     * Out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes): array => ['stock' => 0]);
    }
}
