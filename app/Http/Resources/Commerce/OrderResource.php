<?php

declare(strict_types=1);

namespace App\Http\Resources\Commerce;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of an order.
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'payment_status' => $this->payment_status->value,
            'shipping_status' => $this->shipping_status->value,
            'currency' => $this->currency,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'shipping_amount' => $this->shipping_amount,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'placed_at' => $this->created_at?->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(static fn (OrderItem $item): array => [
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
                'attributes' => $item->attributes,
            ])->all()),
            'addresses' => $this->whenLoaded('addresses', fn () => $this->addresses->map(static fn (OrderAddress $address): array => [
                'type' => $address->type->value,
                'name' => $address->name,
                'phone' => $address->phone,
                'address_line_1' => $address->address_line_1,
                'address_line_2' => $address->address_line_2,
                'city' => $address->city,
                'state' => $address->state,
                'postal_code' => $address->postal_code,
                'country_code' => $address->country_code,
            ])->all()),
            'payment' => $this->whenLoaded('payments', fn () => $this->payments->map(static fn (Payment $payment): array => [
                'gateway' => $payment->gateway->value,
                'status' => $payment->status->value,
                'amount' => $payment->amount,
            ])->all()),
        ];
    }
}
