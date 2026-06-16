<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SmsLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SmsLog>
 */
class SmsLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'to' => '01'.fake()->numberBetween(3, 9).fake()->numerify('########'),
            'message' => fake()->sentence(),
            'provider' => 'log',
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }
}
