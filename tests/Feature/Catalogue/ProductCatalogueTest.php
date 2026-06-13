<?php

declare(strict_types=1);

namespace Tests\Feature\Catalogue;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogueTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_active_products(): void
    {
        $active = Product::factory()->create();
        ProductVariant::factory()->for($active)->create(['price' => 1200]);

        Product::factory()->draft()->create();
        Product::factory()->archived()->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $active->slug)
            ->assertJsonStructure(['data' => [['id', 'name', 'slug', 'price_from']], 'meta', 'links']);
    }

    public function test_it_returns_lowest_active_variant_price_in_listing(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create(['price' => 2500]);
        ProductVariant::factory()->for($product)->create(['price' => 1499]);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk();
        $this->assertEquals(1499, (int) $response->json('data.0.price_from'));
    }

    public function test_it_filters_products_by_price_range(): void
    {
        $cheap = Product::factory()->create();
        ProductVariant::factory()->for($cheap)->create(['price' => 500]);

        $expensive = Product::factory()->create();
        ProductVariant::factory()->for($expensive)->create(['price' => 9000]);

        $response = $this->getJson('/api/v1/products?min_price=1000&max_price=5000');

        $response->assertOk()->assertJsonCount(0, 'data');

        $this->getJson('/api/v1/products?max_price=1000')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $cheap->slug);
    }

    public function test_it_sorts_products_by_price_ascending(): void
    {
        $a = Product::factory()->create();
        ProductVariant::factory()->for($a)->create(['price' => 3000]);

        $b = Product::factory()->create();
        ProductVariant::factory()->for($b)->create(['price' => 1000]);

        $response = $this->getJson('/api/v1/products?sort=price_asc');

        $response->assertOk()
            ->assertJsonPath('data.0.slug', $b->slug)
            ->assertJsonPath('data.1.slug', $a->slug);
    }

    public function test_it_rejects_invalid_filter_input(): void
    {
        $this->getJson('/api/v1/products?sort=cheapest&per_page=999')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sort', 'per_page']);
    }

    public function test_it_shows_a_single_active_product_with_detail(): void
    {
        $product = Product::factory()->create();
        ProductImage::factory()->for($product)->primary()->create();
        ProductVariant::factory()->for($product)->create();

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.slug', $product->slug)
            ->assertJsonStructure(['data' => ['id', 'name', 'description', 'images', 'variants', 'meta']]);
    }

    public function test_it_returns_404_for_a_draft_product(): void
    {
        $product = Product::factory()->draft()->create();

        $this->getJson("/api/v1/products/{$product->slug}")->assertNotFound();
    }

    public function test_it_returns_404_for_unknown_product(): void
    {
        $this->getJson('/api/v1/products/does-not-exist')->assertNotFound();
    }

    public function test_it_lists_active_variants_for_a_product(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create(['price' => 1000]);
        ProductVariant::factory()->for($product)->inactive()->create();

        $response = $this->getJson("/api/v1/products/{$product->slug}/variants");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['data' => [['id', 'sku', 'price', 'in_stock']]]);
    }
}
