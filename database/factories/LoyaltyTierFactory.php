<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LoyaltyTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyTier>
 */
class LoyaltyTierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Bronze', 'Silver', 'Gold', 'Platinum']),
            'min_points' => fake()->randomElement([0, 500, 2000, 5000]),
            'discount_percent' => fake()->randomElement([0, 5, 10, 15]),
            'perks' => ['free_shipping' => true],
        ];
    }
}
