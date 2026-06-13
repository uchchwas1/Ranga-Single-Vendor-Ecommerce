<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LoginActivity;
use App\Models\User;
use App\Support\Enums\LoginStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * Factory for LoginActivity models.
 *
 * @extends Factory<LoginActivity>
 */
class LoginActivityFactory extends Factory
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
            'ip' => fake()->ipv4(),
            'device' => 'desktop',
            'browser' => 'Chrome',
            'os' => 'Linux',
            'status' => LoginStatus::Success,
            'created_at' => Date::now(),
        ];
    }
}
