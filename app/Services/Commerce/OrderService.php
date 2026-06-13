<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Events\Commerce\OrderPlaced;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryContract;
use App\Services\Payment\PaymentService;
use App\Support\Dto\CheckoutResult;
use App\Support\Enums\AddressType;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\PaymentGateway;
use App\Support\Enums\PaymentStatus;
use App\Support\Enums\ShippingStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Application service that turns a cart into a placed order and kicks
 * off payment. All business logic lives here, never in controllers.
 */
class OrderService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly OrderRepositoryContract $orders,
        private readonly CartService $carts,
        private readonly ShippingService $shipping,
        private readonly PaymentService $payments,
    ) {
    }

    /**
     * Place an order from the given cart.
     *
     * @param  array{shipping: array<string, mixed>, billing?: array<string, mixed>|null, shipping_method: string, payment_gateway: string, guest_email?: string|null, notes?: string|null}  $data
     *
     * @throws ValidationException
     */
    public function place(Cart $cart, array $data, ?User $user, ?string $ip = null, ?string $userAgent = null): CheckoutResult
    {
        $cart->load(['items.product.primaryImage', 'items.variant.attributeValues.attribute']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => [__('commerce.checkout.empty_cart')]]);
        }

        if ($user === null && empty($data['guest_email'])) {
            throw ValidationException::withMessages(['guest_email' => [__('commerce.checkout.guest_email_required')]]);
        }

        $gateway = PaymentGateway::tryFrom($data['payment_gateway']);

        if ($gateway === null || ! $gateway->isImplemented()) {
            throw ValidationException::withMessages(['payment_gateway' => [__('commerce.checkout.gateway_unsupported')]]);
        }

        $method = $this->shipping->findByCode($data['shipping_method']);

        if ($method === null) {
            throw ValidationException::withMessages(['shipping_method' => [__('commerce.checkout.shipping_unavailable')]]);
        }

        $subtotal = $cart->subtotal();
        $weight = $this->cartWeight($cart);
        $shippingCost = $this->shipping->quote($method, $subtotal, $weight);
        $taxRate = (float) config('ranga.tax.rate', 0);
        $taxAmount = round($subtotal * $taxRate, 2);
        $total = round($subtotal + $shippingCost + $taxAmount, 2);

        $result = DB::transaction(function () use ($cart, $data, $user, $ip, $userAgent, $gateway, $subtotal, $shippingCost, $taxAmount, $taxRate, $total): CheckoutResult {
            $order = $this->orders->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user?->id,
                'guest_email' => $user === null ? ($data['guest_email'] ?? null) : null,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Pending,
                'shipping_status' => ShippingStatus::Pending,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'shipping_amount' => $shippingCost,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'currency' => $cart->currency,
                'notes' => $data['notes'] ?? null,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);

            $this->createItems($order, $cart, $taxRate);
            $this->createAddresses($order, $data);

            $payment = $order->payments()->create([
                'user_id' => $user?->id,
                'gateway' => $gateway,
                'amount' => $total,
                'currency' => $cart->currency,
                'status' => PaymentStatus::Pending,
            ]);

            $initiation = $this->payments->initiate($payment);

            $this->carts->clear($cart);

            OrderPlaced::dispatch($order);

            return new CheckoutResult($order->refresh()->load(['items', 'addresses', 'payments']), $initiation);
        });

        return $result;
    }

    /**
     * Snapshot cart lines into order items.
     */
    private function createItems(Order $order, Cart $cart, float $taxRate): void
    {
        $cart->items->each(function (CartItem $item) use ($order, $taxRate): void {
            $lineSubtotal = $item->lineTotal();

            $order->items()->create([
                'product_id' => $item->product_id,
                'variant_id' => $item->variant_id,
                'product_name' => $item->product?->name ?? __('commerce.checkout.unknown_product'),
                'sku' => $item->variant?->sku ?? $item->product?->sku,
                'image' => $item->product?->primaryImage?->image_path,
                'quantity' => $item->quantity,
                'unit_price' => $item->price_at_add,
                'discount' => 0,
                'subtotal' => $lineSubtotal,
                'tax_rate' => $taxRate * 100,
                'tax_amount' => round($lineSubtotal * $taxRate, 2),
                'attributes' => $this->variantAttributes($item),
            ]);
        });
    }

    /**
     * Persist shipping (and billing) addresses for the order.
     *
     * @param  array{shipping: array<string, mixed>, billing?: array<string, mixed>|null}  $data
     */
    private function createAddresses(Order $order, array $data): void
    {
        $order->addresses()->create($this->addressAttributes(AddressType::Shipping, $data['shipping']));

        $billing = $data['billing'] ?? $data['shipping'];
        $order->addresses()->create($this->addressAttributes(AddressType::Billing, $billing));
    }

    /**
     * Normalise an address payload for persistence.
     *
     * @param  array<string, mixed>  $address
     * @return array<string, mixed>
     */
    private function addressAttributes(AddressType $type, array $address): array
    {
        return [
            'type' => $type,
            'name' => $address['name'] ?? '',
            'phone' => $address['phone'] ?? '',
            'address_line_1' => $address['address_line_1'] ?? '',
            'address_line_2' => $address['address_line_2'] ?? null,
            'city' => $address['city'] ?? '',
            'state' => $address['state'] ?? null,
            'postal_code' => $address['postal_code'] ?? null,
            'country_code' => $address['country_code'] ?? 'BD',
        ];
    }

    /**
     * Build an attribute snapshot for a cart line's variant.
     *
     * @return array<string, string>
     */
    private function variantAttributes(CartItem $item): array
    {
        if ($item->variant === null) {
            return [];
        }

        $attributes = [];

        foreach ($item->variant->attributeValues as $value) {
            $name = $value->relationLoaded('attribute') && $value->attribute !== null
                ? $value->attribute->name
                : $value->attribute_id;
            $attributes[$name] = $value->value;
        }

        return $attributes;
    }

    /**
     * Total physical weight of the cart (kg).
     */
    private function cartWeight(Cart $cart): float
    {
        return (float) $cart->items->sum(static function (CartItem $item): float {
            $weight = $item->variant?->weight ?? $item->product?->weight ?? 0;

            return (float) $weight * $item->quantity;
        });
    }

    /**
     * Generate a unique, human-readable order number.
     */
    private function generateOrderNumber(): string
    {
        do {
            $number = 'RNG-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while ($this->orders->findByNumber($number) !== null);

        return $number;
    }
}
