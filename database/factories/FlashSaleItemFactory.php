<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlashSaleItem>
 */
class FlashSaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'flash_sale_id' => FlashSale::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'sale_price' => fake()->randomFloat(2, 200, 2000),
            'quantity_limit' => null,
            'sold_count' => 0,
        ];
    }
}
