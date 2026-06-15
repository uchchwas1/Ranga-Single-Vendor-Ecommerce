<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ReferralReward;
use App\Models\User;
use App\Support\Enums\ReferralRewardStatus;
use App\Support\Enums\ReferralRewardType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferralReward>
 */
class ReferralRewardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'referrer_id' => User::factory(),
            'referred_id' => User::factory(),
            'order_id' => null,
            'reward_type' => ReferralRewardType::Points,
            'reward_value' => 100,
            'status' => ReferralRewardStatus::Granted,
        ];
    }
}
