<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use App\Support\Enums\SubscriptionInterval;
use App\Support\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'variant_id' => null,
            'interval' => SubscriptionInterval::Monthly,
            'status' => SubscriptionStatus::Active,
            'price' => fake()->randomFloat(2, 500, 3000),
            'quantity' => 1,
            'started_at' => Date::now(),
            'next_billing_at' => Date::now()->addMonth(),
        ];
    }

    /**
     * A subscription overdue for renewal.
     */
    public function due(): static
    {
        return $this->state(fn (array $attributes): array => [
            'next_billing_at' => Date::now()->subDay(),
        ]);
    }
}
