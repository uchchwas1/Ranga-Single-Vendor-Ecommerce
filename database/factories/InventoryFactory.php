<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inventory>
 */
class InventoryFactory extends Factory
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
            'variant_id' => ProductVariant::factory(),
            'warehouse_id' => Warehouse::factory(),
            'quantity' => fake()->numberBetween(10, 200),
            'reserved_quantity' => 0,
            'low_stock_threshold' => 5,
        ];
    }

    /**
     * A row sitting at or below its low-stock threshold.
     */
    public function low(): static
    {
        return $this->state(fn (array $attributes): array => [
            'quantity' => 2,
            'low_stock_threshold' => 5,
        ]);
    }
}
