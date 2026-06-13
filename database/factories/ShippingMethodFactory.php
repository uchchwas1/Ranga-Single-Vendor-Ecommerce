<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Standard Delivery', 'Express Delivery', 'Inside Dhaka']);

        return [
            'name' => $name,
            'code' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => fake()->sentence(),
            'carrier' => fake()->randomElement(['Pathao', 'RedX', 'Steadfast']),
            'min_order_amount' => 0,
            'max_weight' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    /**
     * Attach a default rate to the method after creation.
     */
    public function withRate(float $baseRate = 60, ?float $freeAbove = null): static
    {
        return $this->afterCreating(function (ShippingMethod $method) use ($baseRate, $freeAbove): void {
            $method->rates()->create([
                'shipping_zone_id' => null,
                'base_rate' => $baseRate,
                'per_kg_rate' => 0,
                'free_above_amount' => $freeAbove,
                'estimated_days_min' => 1,
                'estimated_days_max' => 5,
            ]);
        });
    }
}
