<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\ShippingLabel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingLabel>
 */
class ShippingLabelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'shipment_id' => null,
            'label_url' => fake()->url(),
        ];
    }
}
