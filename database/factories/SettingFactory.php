<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for Setting models.
 *
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => 'general',
            'key' => fake()->unique()->slug(2),
            'value' => fake()->word(),
            'is_public' => false,
        ];
    }

    /**
     * Indicate that the setting is publicly exposable.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_public' => true,
        ]);
    }
}
