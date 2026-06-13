<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;

/**
 * Application service for customer wishlists.
 */
class WishlistService
{
    /**
     * The user's wishlist entries with product detail.
     *
     * @return Collection<int, Wishlist>
     */
    public function list(User $user): Collection
    {
        return $user->wishlists()
            ->with(['product.primaryImage', 'variant'])
            ->latest()
            ->get();
    }

    /**
     * Add a product (optionally a variant) to the wishlist. Idempotent.
     */
    public function add(User $user, Product $product, ?string $variantId = null): Wishlist
    {
        /** @var Wishlist $wishlist */
        $wishlist = Wishlist::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'variant_id' => $variantId,
            ],
            ['added_at' => Date::now()],
        );

        return $wishlist;
    }

    /**
     * Remove a product from the wishlist. Returns whether anything was removed.
     */
    public function remove(User $user, string $productId, ?string $variantId = null): bool
    {
        return $user->wishlists()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->delete() > 0;
    }
}
