<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'parent_id' => null,
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'description' => fake()->sentence(),
            'image' => null,
            'icon' => null,
            'meta_title' => Str::title($name),
            'meta_description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }

    /**
     * Indicate the category is a child of the given parent.
     */
    public function childOf(Category $parent): static
    {
        return $this->state(fn (array $attributes): array => ['parent_id' => $parent->id]);
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }
}
