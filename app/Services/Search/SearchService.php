<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Models\Product;
use App\Support\Dto\ProductFilters;
use App\Support\Enums\ProductStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Provider-agnostic full-text search over the catalogue.
 *
 * Backed by Laravel Scout (Meilisearch in production). Never issues
 * LIKE queries against the products table directly (blueprint 2.12).
 */
class SearchService
{
    /**
     * Full-text product search with storefront filters applied.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function search(string $query, ProductFilters $filters): LengthAwarePaginator
    {
        $builder = Product::search(trim($query))
            ->where('status', ProductStatus::Active->value);

        if ($filters->categoryId !== null) {
            $builder->where('category_id', $filters->categoryId);
        }

        if ($filters->brandId !== null) {
            $builder->where('brand_id', $filters->brandId);
        }

        /** @var LengthAwarePaginator<int, Product> $results */
        $results = $builder->paginate($filters->perPage);

        $results->getCollection()->load(['primaryImage', 'brand', 'category']);

        return $results;
    }

    /**
     * Lightweight name suggestions for an instant-search dropdown.
     *
     * @return list<array{slug: string, name: string}>
     */
    public function suggestions(string $query, int $limit = 8): array
    {
        $trimmed = trim($query);

        if ($trimmed === '') {
            return [];
        }

        return Product::search($trimmed)
            ->where('status', ProductStatus::Active->value)
            ->take($limit)
            ->get()
            ->map(static fn (Product $product): array => [
                'slug' => $product->slug,
                'name' => $product->name,
            ])
            ->all();
    }
}
