<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Page;
use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoMeta>
 */
class SeoMetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model_type' => Page::class,
            'model_id' => (string) \Illuminate\Support\Str::ulid(),
            'title' => fake()->sentence(4),
            'description' => fake()->sentence(),
            'keywords' => implode(',', fake()->words(4)),
            'og_image' => null,
            'schema_markup' => null,
            'canonical_url' => null,
        ];
    }
}
