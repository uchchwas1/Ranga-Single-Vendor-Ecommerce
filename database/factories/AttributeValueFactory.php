<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributeValue>
 */
class AttributeValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'attribute_id' => Attribute::factory(),
            'value' => fake()->unique()->word(),
            'meta' => null,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    /**
     * A colour value carrying a hex swatch.
     */
    public function color(string $value = 'Red', string $hex = '#ff0000'): static
    {
        return $this->state(fn (array $attributes): array => [
            'value' => $value,
            'meta' => ['hex' => $hex],
        ]);
    }
}
