<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LoyaltyTier;
use Illuminate\Database\Seeder;

/**
 * Seeds the default loyalty tier ladder.
 */
class LoyaltyTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tiers = [
            ['name' => 'Bronze', 'min_points' => 0, 'discount_percent' => 0],
            ['name' => 'Silver', 'min_points' => 500, 'discount_percent' => 5],
            ['name' => 'Gold', 'min_points' => 2000, 'discount_percent' => 10],
            ['name' => 'Platinum', 'min_points' => 5000, 'discount_percent' => 15],
        ];

        foreach ($tiers as $tier) {
            LoyaltyTier::query()->updateOrCreate(
                ['name' => $tier['name']],
                [
                    'min_points' => $tier['min_points'],
                    'discount_percent' => $tier['discount_percent'],
                    'perks' => ['birthday_bonus' => true],
                ],
            );
        }
    }
}
