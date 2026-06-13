<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryContract;
use App\Support\Dto\ProductFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of the product repository.
 *
 * All catalogue reads go through Eloquent / the query builder — no raw SQL
 * and no LIKE queries (full-text search is delegated to Scout).
 */
class EloquentProductRepository implements ProductRepositoryContract
{
    /**
     * Paginate active products matching the given filters.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateActive(ProductFilters $filters): LengthAwarePaginator
    {
        return $this->baseQuery($filters)->paginate($filters->perPage);
    }

    /**
     * Paginate active products within a category (and its descendants).
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateByCategory(string $categoryId, ProductFilters $filters): LengthAwarePaginator
    {
        $categoryIds = $this->descendantCategoryIds($categoryId);

        return $this->baseQuery($filters)
            ->whereIn('category_id', $categoryIds)
            ->paginate($filters->perPage);
    }

    /**
     * Paginate active products for a brand.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateByBrand(string $brandId, ProductFilters $filters): LengthAwarePaginator
    {
        return $this->baseQuery($filters)
            ->where('brand_id', $brandId)
            ->paginate($filters->perPage);
    }

    /**
     * Find a single active product by slug, eager-loading detail relations.
     */
    public function findActiveBySlug(string $slug): ?Product
    {
        return Product::query()
            ->active()
            ->with([
                'category',
                'brand',
                'images',
                'videos',
                'tags',
                'variants' => fn ($q) => $q->where('is_active', true),
                'variants.attributeValues.attribute',
                'variants.image',
            ])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * Featured active products for storefront widgets.
     *
     * @return Collection<int, Product>
     */
    public function featured(int $limit = 10): Collection
    {
        return Product::query()
            ->active()
            ->featured()
            ->with(['primaryImage', 'brand'])
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Base query with filters and sorting applied.
     *
     * @return Builder<Product>
     */
    private function baseQuery(ProductFilters $filters): Builder
    {
        $query = Product::query()
            ->active()
            ->with(['primaryImage', 'brand', 'category'])
            ->withMin(['variants as price_from' => fn (Builder $q) => $q->where('is_active', true)], 'price');

        if ($filters->categoryId !== null) {
            $query->whereIn('category_id', $this->descendantCategoryIds($filters->categoryId));
        }

        if ($filters->brandId !== null) {
            $query->where('brand_id', $filters->brandId);
        }

        if ($filters->featuredOnly) {
            $query->featured();
        }

        if ($filters->minPrice !== null || $filters->maxPrice !== null) {
            $query->whereHas('variants', function (Builder $q) use ($filters): void {
                $q->where('is_active', true);

                if ($filters->minPrice !== null) {
                    $q->where('price', '>=', $filters->minPrice);
                }

                if ($filters->maxPrice !== null) {
                    $q->where('price', '<=', $filters->maxPrice);
                }
            });
        }

        if ($filters->attributeValueIds !== []) {
            $query->whereHas('variants.attributeValues', function (Builder $q) use ($filters): void {
                $q->whereIn('attribute_values.id', $filters->attributeValueIds);
            });
        }

        return $this->applySort($query, $filters->sort);
    }

    /**
     * Apply the requested sort order.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    private function applySort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'price_asc' => $query->orderBy('price_from'),
            'price_desc' => $query->orderByDesc('price_from'),
            'name' => $query->orderBy('name'),
            'featured' => $query->orderByDesc('is_featured')->orderBy('sort_order'),
            default => $query->latest(),
        };
    }

    /**
     * Collect the given category id plus all descendant ids.
     *
     * @return list<string>
     */
    private function descendantCategoryIds(string $categoryId): array
    {
        $ids = [$categoryId];
        $frontier = [$categoryId];

        while ($frontier !== []) {
            /** @var list<string> $children */
            $children = Category::query()
                ->whereIn('parent_id', $frontier)
                ->pluck('id')
                ->all();

            $children = array_values(array_diff($children, $ids));

            if ($children === []) {
                break;
            }

            $ids = array_merge($ids, $children);
            $frontier = $children;
        }

        return $ids;
    }
}
