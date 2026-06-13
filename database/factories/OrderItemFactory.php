<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 500, 5000);
        $qty = fake()->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'product_id' => null,
            'variant_id' => null,
            'product_name' => fake()->words(3, true),
            'sku' => 'SKU-'.Str::upper(Str::random(8)),
            'image' => null,
            'quantity' => $qty,
            'unit_price' => $price,
            'discount' => 0,
            'subtotal' => $price * $qty,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'attributes' => ['Colour' => 'Red', 'Size' => 'M'],
        ];
    }
}
