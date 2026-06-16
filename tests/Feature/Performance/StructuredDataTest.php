<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_detail_includes_jsonld_and_seo_meta(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        ProductImage::factory()->for($product)->primary()->create();
        ProductVariant::factory()->for($product)->create(['price' => 1500, 'stock' => 5]);

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.structured_data.0.@type', 'Product')
            ->assertJsonPath('data.structured_data.1.@type', 'BreadcrumbList')
            ->assertJsonPath('data.seo.canonical', url('/products/'.$product->slug));

        // Offer reflects availability + currency.
        $this->assertSame('InStock', class_basename((string) $response->json('data.structured_data.0.offers.availability')));
    }
}
