<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Affiliate;
use App\Models\User;
use App\Support\Enums\AffiliateStatus;
use App\Support\Enums\CommissionType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Affiliate>
 */
class AffiliateFactory extends Factory
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
            'code' => Str::upper(Str::random(8)),
            'commission_rate' => 10,
            'commission_type' => CommissionType::Percent,
            'earnings_total' => 0,
            'paid_total' => 0,
            'status' => AffiliateStatus::Active,
        ];
    }

    /**
     * A pending (not yet approved) affiliate.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => AffiliateStatus::Pending]);
    }
}
