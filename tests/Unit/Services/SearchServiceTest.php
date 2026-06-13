<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Search\SearchService;
use App\Support\Dto\ProductFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): SearchService
    {
        return app(SearchService::class);
    }

    public function test_search_returns_a_paginator_of_matching_products(): void
    {
        $product = Product::factory()->create(['name' => 'Saffron Lehenga Set']);
        ProductVariant::factory()->for($product)->create();

        $results = $this->service()->search('Saffron', new ProductFilters());

        $this->assertSame(1, $results->total());
        $this->assertSame($product->id, $results->items()[0]->id);
    }

    public function test_suggestions_returns_an_empty_list_for_a_blank_query(): void
    {
        $this->assertSame([], $this->service()->suggestions('   '));
    }

    public function test_suggestions_return_slug_and_name_pairs(): void
    {
        $product = Product::factory()->create(['name' => 'Teal Anarkali']);
        ProductVariant::factory()->for($product)->create();

        $suggestions = $this->service()->suggestions('Teal');

        $this->assertSame([['slug' => $product->slug, 'name' => 'Teal Anarkali']], $suggestions);
    }
}
