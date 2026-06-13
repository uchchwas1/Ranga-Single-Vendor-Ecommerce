<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'sku' => 'SKU-'.Str::upper(Str::random(8)),
            'barcode' => (string) fake()->ean13(),
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraphs(3, true),
            'specifications' => ['fabric' => 'Cotton', 'fit' => 'Regular'],
            'faqs' => [['q' => 'Is it machine washable?', 'a' => 'Yes.']],
            'meta_title' => Str::title($name),
            'meta_description' => fake()->sentence(),
            'meta_keywords' => implode(',', fake()->words(4)),
            'status' => ProductStatus::Active,
            'published_at' => Date::now()->subDay(),
            'is_featured' => false,
            'is_digital' => false,
            'weight' => fake()->randomFloat(3, 0.1, 2),
            'weight_unit' => 'kg',
            'dimensions' => ['l' => 30, 'w' => 20, 'h' => 5],
            'video_url' => null,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }

    /**
     * A draft (non-public) product.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ProductStatus::Draft,
            'published_at' => null,
        ]);
    }

    /**
     * An archived product.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => ProductStatus::Archived]);
    }

    /**
     * A featured product.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => ['is_featured' => true]);
    }
}
