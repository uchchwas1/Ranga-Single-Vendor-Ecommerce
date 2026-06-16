<?php

declare(strict_types=1);

namespace Tests\Feature\Performance;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CacheInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_tree_is_cached_and_invalidated_on_save(): void
    {
        Category::factory()->create();

        $repository = app(CategoryRepositoryContract::class);

        $this->assertCount(1, $repository->tree());

        // Add a category — the observer should flush the cached tree.
        Category::factory()->create();

        $this->assertCount(2, $repository->tree());
    }

    public function test_the_category_tree_endpoint_reflects_new_categories(): void
    {
        Category::factory()->create();

        $this->getJson('/api/v1/categories')->assertOk()->assertJsonCount(1, 'data');

        Category::factory()->create();

        $this->getJson('/api/v1/categories')->assertOk()->assertJsonCount(2, 'data');
    }
}
