<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalogue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogue\ProductIndexRequest;
use App\Http\Resources\Catalogue\CategoryResource;
use App\Http\Resources\Catalogue\ProductResource;
use App\Services\Catalogue\ProductService;
use App\Support\Dto\ProductFilters;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public category endpoints.
 */
class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ProductService $products,
    ) {
    }

    /**
     * GET /categories — the active category tree.
     */
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection($this->products->categoryTree());
    }

    /**
     * GET /categories/{slug}/products — products within a category.
     */
    public function products(ProductIndexRequest $request, string $slug): AnonymousResourceCollection
    {
        $category = $this->products->findCategoryBySlug($slug);

        if ($category === null) {
            throw new NotFoundHttpException(__('catalogue.category_not_found'));
        }

        $filters = ProductFilters::fromArray($request->validated());

        return ProductResource::collection($this->products->listByCategory($category, $filters));
    }
}
