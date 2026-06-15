<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\AffiliateClick;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AffiliateClick>
 */
class AffiliateClickFactory extends Factory
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
            'ip' => fake()->ipv4(),
            'referrer' => fake()->url(),
            'landing_page' => '/',
        ];
    }
}
