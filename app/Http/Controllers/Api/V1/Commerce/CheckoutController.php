<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\PlaceOrderRequest;
use App\Http\Resources\Commerce\OrderResource;
use App\Models\Payment;
use App\Models\ShippingMethod;
use App\Services\Commerce\CartService;
use App\Services\Commerce\OrderService;
use App\Services\Commerce\ShippingService;
use App\Services\Payment\PaymentService;
use App\Support\Enums\PaymentGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Checkout: shipping quotes, order placement, and gateway callbacks.
 */
class CheckoutController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly CartService $carts,
        private readonly ShippingService $shipping,
        private readonly OrderService $orders,
        private readonly PaymentService $payments,
    ) {
    }

    /**
     * GET /checkout/shipping-methods — available methods + quotes for the cart.
     */
    public function shippingMethods(Request $request): JsonResponse
    {
        $cart = $this->resolveCart($request)->load('items.variant', 'items.product');
        $subtotal = $cart->subtotal();
        $weight = (float) $cart->items->sum(
            static fn ($item): float => (float) ($item->variant?->weight ?? $item->product?->weight ?? 0) * $item->quantity,
        );

        $methods = $this->shipping->availableMethods($subtotal, $weight)
            ->map(fn (ShippingMethod $method): array => [
                'code' => $method->code,
                'name' => $method->name,
                'carrier' => $method->carrier,
                'cost' => $this->shipping->quote($method, $subtotal, $weight),
            ])
            ->all();

        return new JsonResponse(['data' => $methods]);
    }

    /**
     * POST /checkout — place an order from the current cart.
     */
    public function place(PlaceOrderRequest $request): JsonResponse
    {
        $cart = $this->resolveCart($request)->load(['coupon', 'giftCard']);

        /** @var array{shipping: array<string, mixed>, billing?: array<string, mixed>|null, shipping_method: string, payment_gateway: string, guest_email?: string|null, notes?: string|null, coupon_code?: string|null, gift_card_code?: string|null, redeem_points?: int|null, affiliate_code?: string|null} $data */
        $data = $request->validated();

        // Fall back to coupon/gift card already applied to the cart.
        $data['coupon_code'] ??= $cart->coupon?->code;
        $data['gift_card_code'] ??= $cart->giftCard?->code;

        $result = $this->orders->place($cart, $data, $request->user(), $request->ip(), $request->userAgent());

        return new JsonResponse([
            'data' => new OrderResource($result->order),
            'payment' => [
                'gateway' => $result->order->payments->first()?->gateway->value,
                'status' => $result->initiation->status->value,
                'requires_redirect' => $result->initiation->requiresRedirect,
                'redirect_url' => $result->initiation->redirectUrl,
                'message' => $result->initiation->message,
            ],
        ], 201);
    }

    /**
     * POST/GET /checkout/payment/{gateway}/callback — gateway return handler.
     */
    public function callback(Request $request, string $gateway): JsonResponse
    {
        $enum = PaymentGateway::tryFrom($gateway);

        if ($enum === null || ! $enum->isImplemented()) {
            throw new NotFoundHttpException(__('commerce.checkout.gateway_unsupported'));
        }

        $payment = $this->locatePayment($request);

        if ($payment === null) {
            throw new NotFoundHttpException(__('commerce.checkout.payment_not_found'));
        }

        $verification = $this->payments->gateway($enum)->webhook($request);
        $this->payments->capture($payment, $verification);

        return new JsonResponse([
            'data' => new OrderResource($payment->order->refresh()->load(['items', 'payments'])),
            'payment_status' => $verification->status->value,
            'successful' => $verification->successful,
        ]);
    }

    /**
     * Resolve the active cart from the user or cart token.
     */
    private function resolveCart(Request $request): \App\Models\Cart
    {
        $token = $request->header('X-Cart-Token') ?? $request->input('cart_token');

        return $this->carts->resolve($request->user(), is_string($token) ? $token : null);
    }

    /**
     * Locate the payment referenced by a gateway callback.
     */
    private function locatePayment(Request $request): ?Payment
    {
        // SSLCommerz round-trips our payment id as tran_id.
        $tranId = $request->input('tran_id');

        if (is_string($tranId)) {
            $payment = Payment::query()->with('order')->find($tranId);

            if ($payment !== null) {
                return $payment;
            }
        }

        // bKash / Stripe round-trip the gateway transaction id we stored.
        $ref = $request->input('paymentID') ?? $request->input('session_id') ?? $request->input('val_id');

        if (is_string($ref)) {
            return Payment::query()->with('order')->where('gateway_transaction_id', $ref)->first();
        }

        return null;
    }
}
