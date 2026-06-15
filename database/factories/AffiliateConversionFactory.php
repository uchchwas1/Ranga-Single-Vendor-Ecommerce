<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\AffiliateConversion;
use App\Models\Order;
use App\Support\Enums\ConversionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliateConversion>
 */
class AffiliateConversionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'affiliate_id' => Affiliate::factory(),
            'order_id' => Order::factory(),
            'commission' => fake()->randomFloat(2, 50, 500),
            'status' => ConversionStatus::Pending,
        ];
    }
}
