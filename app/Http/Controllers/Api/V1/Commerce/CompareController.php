<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalogue\ProductResource;
use App\Models\Product;
use App\Models\ProductComparison;
use App\Services\Commerce\ComparisonService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Product comparison list for guests (token-based) and users.
 */
class CompareController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ComparisonService $comparisons,
    ) {
    }

    /**
     * GET /products/compare — products in the comparison list.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $comparison = $this->resolve($request);

        return ProductResource::collection($this->comparisons->products($comparison));
    }

    /**
     * POST /products/compare/{product} — add a product.
     */
    public function store(Request $request, Product $product): AnonymousResourceCollection
    {
        $comparison = $this->resolve($request);
        $this->comparisons->add($comparison, $product->id);

        return ProductResource::collection($this->comparisons->products($comparison));
    }

    /**
     * DELETE /products/compare/{product} — remove a product.
     */
    public function destroy(Request $request, Product $product): AnonymousResourceCollection
    {
        $comparison = $this->resolve($request);
        $this->comparisons->remove($comparison, $product->id);

        return ProductResource::collection($this->comparisons->products($comparison));
    }

    /**
     * Resolve the comparison list from the user or compare token.
     */
    private function resolve(Request $request): ProductComparison
    {
        $token = $request->header('X-Compare-Token') ?? $request->input('compare_token');

        return $this->comparisons->resolve($request->user(), is_string($token) ? $token : null);
    }
}
