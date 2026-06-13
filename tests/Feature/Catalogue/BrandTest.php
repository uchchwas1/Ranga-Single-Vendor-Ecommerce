<?php

declare(strict_types=1);

namespace Tests\Feature\Catalogue;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_active_brands(): void
    {
        Brand::factory()->create(['name' => 'Ranga Signature']);
        Brand::factory()->inactive()->create();

        $response = $this->getJson('/api/v1/brands');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure(['data' => [['id', 'name', 'slug']]]);
    }

    public function test_it_lists_products_for_a_brand(): void
    {
        $brand = Brand::factory()->create();
        $product = Product::factory()->create(['brand_id' => $brand->id]);
        ProductVariant::factory()->for($product)->create(['price' => 1500]);

        Product::factory()->create(); // different brand

        $response = $this->getJson("/api/v1/brands/{$brand->slug}/products");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $product->slug);
    }

    public function test_it_returns_404_for_unknown_brand(): void
    {
        $this->getJson('/api/v1/brands/nope/products')->assertNotFound();
    }
}
