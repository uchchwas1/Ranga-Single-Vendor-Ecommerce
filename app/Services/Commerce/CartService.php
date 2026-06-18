<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SavedCartItem;
use App\Models\User;
use App\Repositories\Contracts\CartRepositoryContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Application service for cart lifecycle and "save for later".
 */
class CartService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly CartRepositoryContract $carts,
    ) {
    }

    /**
     * Resolve the active cart for the current actor.
     *
     * Authenticated users get a user-scoped cart; guests get a cart keyed
     * by an opaque session token (generated when absent).
     */
    public function resolve(?User $user, ?string $token): Cart
    {
        if ($user !== null) {
            return $this->carts->forUser($user->id);
        }

        return $this->carts->forSession($token ?? (string) Str::ulid());
    }

    /**
     * Add (or increment) a line item.
     *
     * @throws ValidationException
     */
    public function addItem(Cart $cart, string $productId, ?string $variantId, int $quantity): CartItem
    {
        $quantity = max(1, $quantity);

        $product = Product::query()->active()->find($productId);

        if ($product === null) {
            throw ValidationException::withMessages(['product_id' => [__('commerce.cart.product_unavailable')]]);
        }

        $variant = $this->resolveVariant($product, $variantId);
        $price = $variant !== null ? (float) $variant->price : ($product->priceFrom() ?? 0.0);

        return DB::transaction(function () use ($cart, $product, $variant, $quantity, $price): CartItem {
            /** @var CartItem|null $existing */
            $existing = $cart->items()
                ->where('product_id', $product->id)
                ->where('variant_id', $variant?->id)
                ->first();

            $desired = ($existing?->quantity ?? 0) + $quantity;

            // Pre-order products may be ordered beyond on-hand stock.
            if (! $product->is_preorder) {
                $this->assertStock($variant, $desired);
            }

            if ($existing !== null) {
                $existing->update(['quantity' => $desired]);

                return $existing;
            }

            /** @var CartItem $item */
            $item = $cart->items()->create([
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $quantity,
                'price_at_add' => $price,
            ]);

            return $item;
        });
    }

    /**
     * Update a line item's quantity.
     *
     * @throws ValidationException
     */
    public function updateItem(Cart $cart, string $itemId, int $quantity): CartItem
    {
        $item = $this->lineOrFail($cart, $itemId);
        $quantity = max(1, $quantity);

        $this->assertStock($item->variant, $quantity);

        $item->update(['quantity' => $quantity]);

        return $item;
    }

    /**
     * Remove a line item from the cart.
     */
    public function removeItem(Cart $cart, string $itemId): void
    {
        $this->lineOrFail($cart, $itemId)->delete();
    }

    /**
     * Empty the cart.
     */
    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * Move a cart line into the user's "save for later" list.
     *
     * @throws ValidationException
     */
    public function saveForLater(Cart $cart, string $itemId, User $user): SavedCartItem
    {
        $item = $this->lineOrFail($cart, $itemId);

        return DB::transaction(function () use ($item, $user): SavedCartItem {
            /** @var SavedCartItem $saved */
            $saved = SavedCartItem::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                ],
                ['quantity' => $item->quantity],
            );

            $item->delete();

            return $saved;
        });
    }

    /**
     * Move a saved item back into the active cart.
     *
     * @throws ValidationException
     */
    public function moveSavedToCart(Cart $cart, string $savedId, User $user): CartItem
    {
        /** @var SavedCartItem|null $saved */
        $saved = SavedCartItem::query()->where('user_id', $user->id)->find($savedId);

        if ($saved === null) {
            throw ValidationException::withMessages(['saved_id' => [__('commerce.cart.saved_not_found')]]);
        }

        $item = $this->addItem($cart, $saved->product_id, $saved->variant_id, $saved->quantity);
        $saved->delete();

        return $item;
    }

    /**
     * Resolve a variant for the product, validating it belongs and is active.
     *
     * @throws ValidationException
     */
    private function resolveVariant(Product $product, ?string $variantId): ?ProductVariant
    {
        if ($variantId === null) {
            return null;
        }

        /** @var ProductVariant|null $variant */
        $variant = ProductVariant::query()
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->find($variantId);

        if ($variant === null) {
            throw ValidationException::withMessages(['variant_id' => [__('commerce.cart.variant_unavailable')]]);
        }

        return $variant;
    }

    /**
     * Ensure the requested quantity is available for the variant.
     *
     * @throws ValidationException
     */
    private function assertStock(?ProductVariant $variant, int $quantity): void
    {
        if ($variant !== null && $quantity > $variant->stock) {
            throw ValidationException::withMessages([
                'quantity' => [__('commerce.cart.insufficient_stock', ['available' => $variant->stock])],
            ]);
        }
    }

    /**
     * Fetch a line item that belongs to the cart, or fail validation.
     *
     * @throws ValidationException
     */
    private function lineOrFail(Cart $cart, string $itemId): CartItem
    {
        /** @var CartItem|null $item */
        $item = $cart->items()->with('variant')->find($itemId);

        if ($item === null) {
            throw ValidationException::withMessages(['item' => [__('commerce.cart.item_not_found')]]);
        }

        return $item;
    }
}
