<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Product;
use App\Support\Dto\ProductFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Persistence boundary for Product aggregates.
 */
interface ProductRepositoryContract
{
    /**
     * Paginate active products matching the given filters.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateActive(ProductFilters $filters): LengthAwarePaginator;

    /**
     * Paginate active products within a category (and its descendants).
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateByCategory(string $categoryId, ProductFilters $filters): LengthAwarePaginator;

    /**
     * Paginate active products for a brand.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginateByBrand(string $brandId, ProductFilters $filters): LengthAwarePaginator;

    /**
     * Find a single active product by slug, eager-loading detail relations.
     */
    public function findActiveBySlug(string $slug): ?Product;

    /**
     * Featured active products for storefront widgets.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Product>
     */
    public function featured(int $limit = 10): \Illuminate\Database\Eloquent\Collection;
}
