<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Ai;

use App\Http\Controllers\Controller;
use App\Http\Resources\Catalogue\ProductResource;
use App\Repositories\Contracts\ProductRepositoryContract;
use App\Services\AI\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Product recommendation endpoints.
 */
class RecommendationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly RecommendationService $recommendations,
        private readonly ProductRepositoryContract $products,
    ) {
    }

    /**
     * GET /recommendations — personalised (or featured) recommendations.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return ProductResource::collection($this->recommendations->forUser($request->user()));
    }

    /**
     * GET /products/{slug}/recommendations — related products.
     */
    public function related(string $slug): AnonymousResourceCollection
    {
        $product = $this->products->findActiveBySlug($slug);

        if ($product === null) {
            throw new NotFoundHttpException(__('catalogue.product_not_found'));
        }

        return ProductResource::collection($this->recommendations->related($product));
    }
}
