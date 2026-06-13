<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Commerce\WishlistItemResource;
use App\Models\Product;
use App\Models\User;
use App\Services\Commerce\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Authenticated customer wishlist endpoints.
 */
class WishlistController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly WishlistService $wishlist,
    ) {
    }

    /**
     * GET /profile/wishlist — list wishlist entries.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        return WishlistItemResource::collection($this->wishlist->list($user));
    }

    /**
     * POST /profile/wishlist/{product} — add a product.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $variantId = $request->input('variant_id');
        $this->wishlist->add($user, $product, is_string($variantId) ? $variantId : null);

        return new JsonResponse(['message' => __('commerce.wishlist.added')], 201);
    }

    /**
     * DELETE /profile/wishlist/{product} — remove a product.
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $variantId = $request->input('variant_id');
        $this->wishlist->remove($user, $product->id, is_string($variantId) ? $variantId : null);

        return new JsonResponse(['message' => __('commerce.wishlist.removed')]);
    }
}
