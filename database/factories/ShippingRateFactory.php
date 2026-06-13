<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingRate>
 */
class ShippingRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_method_id' => ShippingMethod::factory(),
            'shipping_zone_id' => null,
            'base_rate' => fake()->randomElement([60, 100, 120]),
            'per_kg_rate' => 0,
            'free_above_amount' => null,
            'estimated_days_min' => 1,
            'estimated_days_max' => 5,
        ];
    }
}
