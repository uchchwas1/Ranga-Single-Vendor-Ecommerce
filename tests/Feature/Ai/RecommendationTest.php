<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_receive_featured_recommendations(): void
    {
        $featured = Product::factory()->featured()->create();
        ProductVariant::factory()->for($featured)->create();
        Product::factory()->create(); // non-featured

        $this->getJson('/api/v1/recommendations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $featured->slug);
    }

    public function test_related_products_share_the_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $related = Product::factory()->create(['category_id' => $category->id]);
        ProductVariant::factory()->for($related)->create();

        Product::factory()->create(); // different category

        $this->getJson("/api/v1/products/{$product->slug}/recommendations")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $related->slug);
    }
}
