<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * Factory for User models.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '01'.fake()->numberBetween(3, 9).fake()->numerify('########'),
            'email_verified_at' => Date::now(),
            'password' => static::$password ??= 'password',
            'locale' => 'bn',
            'timezone' => 'Asia/Dhaka',
            'referral_code' => Str::upper(Str::random(10)),
            'loyalty_points' => 0,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the account is deactivated.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the account has confirmed TOTP two-factor auth.
     */
    public function withTwoFactor(string $secret = 'JBSWY3DPEHPK3PXP'): static
    {
        return $this->state(fn (array $attributes): array => [
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => ['RECOVERY01', 'RECOVERY02'],
            'two_factor_confirmed_at' => Date::now(),
        ]);
    }
}
