<?php

declare(strict_types=1);

namespace Tests\Feature\Catalogue;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Exercises Scout search via the "database" driver (configured for the
 * testing environment in phpunit.xml) so no Meilisearch server is needed.
 */
class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_matching_active_products(): void
    {
        $match = Product::factory()->create(['name' => 'Crimson Silk Saree']);
        ProductVariant::factory()->for($match)->create(['price' => 4500]);

        $other = Product::factory()->create(['name' => 'Azure Cotton Kurti']);
        ProductVariant::factory()->for($other)->create(['price' => 1200]);

        $response = $this->getJson('/api/v1/search?q=Crimson');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $match->slug);
    }

    public function test_it_excludes_non_active_products_from_search(): void
    {
        Product::factory()->draft()->create(['name' => 'Crimson Hidden Draft']);

        $response = $this->getJson('/api/v1/search?q=Crimson');

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_search_requires_a_query_term(): void
    {
        $this->getJson('/api/v1/search')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['q']);
    }

    public function test_it_returns_instant_suggestions(): void
    {
        $product = Product::factory()->create(['name' => 'Marigold Maxi Dress']);
        ProductVariant::factory()->for($product)->create();

        $response = $this->getJson('/api/v1/search/suggestions?q=Marigold');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['slug', 'name']]])
            ->assertJsonPath('data.0.name', 'Marigold Maxi Dress');
    }
}
