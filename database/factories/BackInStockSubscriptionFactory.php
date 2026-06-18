<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BackInStockSubscription;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BackInStockSubscription>
 */
class BackInStockSubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'variant_id' => ProductVariant::factory(),
            'user_id' => null,
            'email' => fake()->unique()->safeEmail(),
            'notified_at' => null,
        ];
    }
}
