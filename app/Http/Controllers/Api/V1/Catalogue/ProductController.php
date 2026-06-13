<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalogue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogue\ProductIndexRequest;
use App\Http\Resources\Catalogue\ProductDetailResource;
use App\Http\Resources\Catalogue\ProductResource;
use App\Http\Resources\Catalogue\ProductVariantResource;
use App\Services\Catalogue\ProductService;
use App\Support\Dto\ProductFilters;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public product browsing endpoints.
 */
class ProductController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ProductService $products,
    ) {
    }

    /**
     * GET /products — filtered, sorted, paginated listing.
     */
    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        $filters = ProductFilters::fromArray($request->validated());

        return ProductResource::collection($this->products->list($filters));
    }

    /**
     * GET /products/{slug} — single active product detail.
     */
    public function show(string $slug): ProductDetailResource
    {
        $product = $this->products->findBySlug($slug);

        if ($product === null) {
            throw new NotFoundHttpException(__('catalogue.product_not_found'));
        }

        return new ProductDetailResource($product);
    }

    /**
     * GET /products/{slug}/variants — active variants with stock.
     */
    public function variants(string $slug): AnonymousResourceCollection
    {
        $product = $this->products->findBySlug($slug);

        if ($product === null) {
            throw new NotFoundHttpException(__('catalogue.product_not_found'));
        }

        return ProductVariantResource::collection($product->variants);
    }
}
