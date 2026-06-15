<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return [
            'category_id' => null,
            'user_id' => null,
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'excerpt' => fake()->sentence(),
            'content' => fake()->paragraphs(4, true),
            'featured_image' => null,
            'meta_title' => $title,
            'meta_description' => fake()->sentence(),
            'published_at' => Date::now()->subDay(),
            'view_count' => 0,
        ];
    }

    /**
     * An unpublished draft post.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => ['published_at' => null]);
    }
}
