<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LoyaltyTransaction;
use App\Models\User;
use App\Support\Enums\LoyaltyTransactionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoyaltyTransaction>
 */
class LoyaltyTransactionFactory extends Factory
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
            'order_id' => null,
            'type' => LoyaltyTransactionType::Earn,
            'points' => 50,
            'balance_after' => 50,
            'note' => null,
        ];
    }
}
