<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\AddToCartRequest;
use App\Http\Requests\Commerce\ApplyCouponRequest;
use App\Http\Requests\Commerce\ApplyGiftCardRequest;
use App\Http\Requests\Commerce\UpdateCartItemRequest;
use App\Http\Resources\Commerce\CartResource;
use App\Models\Cart;
use App\Services\Commerce\CartService;
use App\Services\Marketing\CouponService;
use App\Services\Marketing\GiftCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Cart management for guests (token-based) and authenticated users.
 */
class CartController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly CartService $carts,
        private readonly CouponService $coupons,
        private readonly GiftCardService $giftCards,
    ) {
    }

    /**
     * POST /cart/coupon — validate and attach a coupon to the cart.
     */
    public function applyCoupon(ApplyCouponRequest $request): CartResource
    {
        $cart = $this->resolve($request);

        $coupon = $this->coupons->validate(
            (string) $request->validated('code'),
            $cart->subtotal(),
            $request->user(),
        );

        $cart->forceFill(['coupon_id' => $coupon->id])->save();

        return new CartResource($this->withItems($cart));
    }

    /**
     * DELETE /cart/coupon — detach the applied coupon.
     */
    public function removeCoupon(Request $request): CartResource
    {
        $cart = $this->resolve($request);
        $cart->forceFill(['coupon_id' => null])->save();

        return new CartResource($this->withItems($cart));
    }

    /**
     * POST /cart/gift-card — validate and attach a gift card to the cart.
     */
    public function applyGiftCard(ApplyGiftCardRequest $request): CartResource
    {
        $cart = $this->resolve($request);

        $card = $this->giftCards->validate((string) $request->validated('code'));
        $cart->forceFill(['gift_card_id' => $card->id])->save();

        return new CartResource($this->withItems($cart));
    }

    /**
     * GET /cart — view the current cart.
     */
    public function show(Request $request): CartResource
    {
        return new CartResource($this->withItems($this->resolve($request)));
    }

    /**
     * POST /cart/items — add an item.
     */
    public function store(AddToCartRequest $request): JsonResponse
    {
        $cart = $this->resolve($request);

        $this->carts->addItem(
            $cart,
            (string) $request->validated('product_id'),
            $request->validated('variant_id'),
            (int) ($request->validated('quantity') ?? 1),
        );

        return (new CartResource($this->withItems($cart)))->response()->setStatusCode(201);
    }

    /**
     * PUT /cart/items/{item} — update a line quantity.
     */
    public function update(UpdateCartItemRequest $request, string $item): CartResource
    {
        $cart = $this->resolve($request);
        $this->carts->updateItem($cart, $item, (int) $request->validated('quantity'));

        return new CartResource($this->withItems($cart));
    }

    /**
     * DELETE /cart/items/{item} — remove a line.
     */
    public function destroy(Request $request, string $item): CartResource
    {
        $cart = $this->resolve($request);
        $this->carts->removeItem($cart, $item);

        return new CartResource($this->withItems($cart));
    }

    /**
     * DELETE /cart — empty the cart.
     */
    public function clear(Request $request): CartResource
    {
        $cart = $this->resolve($request);
        $this->carts->clear($cart);

        return new CartResource($this->withItems($cart));
    }

    /**
     * POST /cart/items/{item}/save-for-later — park an item (auth only).
     */
    public function saveForLater(Request $request, string $item): CartResource
    {
        $user = $request->user();

        if ($user === null) {
            throw new HttpException(401, __('commerce.cart.login_required'));
        }

        $cart = $this->resolve($request);
        $this->carts->saveForLater($cart, $item, $user);

        return new CartResource($this->withItems($cart));
    }

    /**
     * POST /cart/saved/{saved}/move — move a saved item back to the cart.
     */
    public function moveSaved(Request $request, string $saved): CartResource
    {
        $user = $request->user();

        if ($user === null) {
            throw new HttpException(401, __('commerce.cart.login_required'));
        }

        $cart = $this->resolve($request);
        $this->carts->moveSavedToCart($cart, $saved, $user);

        return new CartResource($this->withItems($cart));
    }

    /**
     * Resolve the current cart from the user or the cart token.
     */
    private function resolve(Request $request): Cart
    {
        $token = $request->header('X-Cart-Token') ?? $request->input('cart_token');

        return $this->carts->resolve($request->user(), is_string($token) ? $token : null);
    }

    /**
     * Reload cart relations needed for the resource.
     */
    private function withItems(Cart $cart): Cart
    {
        return $cart->load(['items.product.primaryImage', 'items.variant', 'coupon', 'giftCard']);
    }
}
