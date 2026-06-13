<?php

declare(strict_types=1);

namespace Tests\Feature\Catalogue;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_the_active_category_tree_with_children(): void
    {
        $parent = Category::factory()->create(['name' => 'Women']);
        Category::factory()->childOf($parent)->create(['name' => 'Sarees']);
        Category::factory()->inactive()->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $parent->slug)
            ->assertJsonCount(1, 'data.0.children');
    }

    public function test_it_lists_products_within_a_category_and_descendants(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->childOf($parent)->create();

        $parentProduct = Product::factory()->create(['category_id' => $parent->id]);
        ProductVariant::factory()->for($parentProduct)->create(['price' => 1000]);

        $childProduct = Product::factory()->create(['category_id' => $child->id]);
        ProductVariant::factory()->for($childProduct)->create(['price' => 1200]);

        $response = $this->getJson("/api/v1/categories/{$parent->slug}/products");

        $response->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_it_returns_404_for_unknown_category(): void
    {
        $this->getJson('/api/v1/categories/nope/products')->assertNotFound();
    }
}
