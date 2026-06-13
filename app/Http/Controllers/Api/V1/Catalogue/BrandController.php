<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalogue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogue\ProductIndexRequest;
use App\Http\Resources\Catalogue\BrandResource;
use App\Http\Resources\Catalogue\ProductResource;
use App\Services\Catalogue\ProductService;
use App\Support\Dto\ProductFilters;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public brand endpoints.
 */
class BrandController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ProductService $products,
    ) {
    }

    /**
     * GET /brands — all active brands.
     */
    public function index(): AnonymousResourceCollection
    {
        return BrandResource::collection($this->products->brands());
    }

    /**
     * GET /brands/{slug}/products — products for a brand.
     */
    public function products(ProductIndexRequest $request, string $slug): AnonymousResourceCollection
    {
        $brand = $this->products->findBrandBySlug($slug);

        if ($brand === null) {
            throw new NotFoundHttpException(__('catalogue.brand_not_found'));
        }

        $filters = ProductFilters::fromArray($request->validated());

        return ProductResource::collection($this->products->listByBrand($brand, $filters));
    }
}
