<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city().' Warehouse',
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'contact_person' => fake()->name(),
            'phone' => '01'.fake()->numberBetween(3, 9).fake()->numerify('########'),
            'is_active' => true,
        ];
    }
}
