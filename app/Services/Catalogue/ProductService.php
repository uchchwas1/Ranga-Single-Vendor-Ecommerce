<?php

declare(strict_types=1);

namespace App\Services\Catalogue;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Repositories\Contracts\BrandRepositoryContract;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Repositories\Contracts\ProductRepositoryContract;
use App\Support\Dto\ProductFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Application service for catalogue browsing. Controllers must only
 * talk to this class, never to models or repositories directly.
 */
class ProductService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly ProductRepositoryContract $products,
        private readonly CategoryRepositoryContract $categories,
        private readonly BrandRepositoryContract $brands,
    ) {
    }

    /**
     * Paginated, filtered product listing.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function list(ProductFilters $filters): LengthAwarePaginator
    {
        return $this->products->paginateActive($filters);
    }

    /**
     * Paginated products within a category.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function listByCategory(Category $category, ProductFilters $filters): LengthAwarePaginator
    {
        return $this->products->paginateByCategory($category->id, $filters);
    }

    /**
     * Paginated products for a brand.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function listByBrand(Brand $brand, ProductFilters $filters): LengthAwarePaginator
    {
        return $this->products->paginateByBrand($brand->id, $filters);
    }

    /**
     * Find a single active product by slug.
     */
    public function findBySlug(string $slug): ?Product
    {
        return $this->products->findActiveBySlug($slug);
    }

    /**
     * Featured products for storefront widgets.
     *
     * @return Collection<int, Product>
     */
    public function featured(int $limit = 10): Collection
    {
        return $this->products->featured($limit);
    }

    /**
     * Find an active category by slug.
     */
    public function findCategoryBySlug(string $slug): ?Category
    {
        return $this->categories->findActiveBySlug($slug);
    }

    /**
     * Find an active brand by slug.
     */
    public function findBrandBySlug(string $slug): ?Brand
    {
        return $this->brands->findActiveBySlug($slug);
    }

    /**
     * The active category tree.
     *
     * @return Collection<int, Category>
     */
    public function categoryTree(): Collection
    {
        return $this->categories->tree();
    }

    /**
     * All active brands.
     *
     * @return Collection<int, Brand>
     */
    public function brands(): Collection
    {
        return $this->brands->allActive();
    }
}
