<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SocialAccount;
use App\Models\User;
use App\Support\Enums\SocialProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for SocialAccount models.
 *
 * @extends Factory<SocialAccount>
 */
class SocialAccountFactory extends Factory
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
            'provider' => fake()->randomElement(SocialProvider::values()),
            'provider_id' => (string) fake()->unique()->numberBetween(1_000_000, 9_999_999),
            'token' => fake()->sha256(),
            'refresh_token' => null,
            'avatar' => fake()->imageUrl(),
        ];
    }
}
