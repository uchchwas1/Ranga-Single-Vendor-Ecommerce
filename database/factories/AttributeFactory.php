<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attribute;
use App\Support\Enums\AttributeType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attribute>
 */
class AttributeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'type' => AttributeType::Text,
            'is_filterable' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    /**
     * A colour attribute.
     */
    public function color(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Colour',
            'type' => AttributeType::Color,
        ]);
    }

    /**
     * A size attribute.
     */
    public function size(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Size',
            'type' => AttributeType::Size,
        ]);
    }
}
