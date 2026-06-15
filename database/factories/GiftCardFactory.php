<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GiftCard;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<GiftCard>
 */
class GiftCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $balance = fake()->randomElement([500, 1000, 2000]);

        return [
            'code' => 'GC-'.Str::upper(Str::random(10)),
            'initial_balance' => $balance,
            'current_balance' => $balance,
            'currency' => 'BDT',
            'expires_at' => null,
            'is_active' => true,
        ];
    }
}
