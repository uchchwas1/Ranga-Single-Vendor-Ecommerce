<?php

declare(strict_types=1);

namespace App\Http\Resources\Commerce;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a cart and its running totals.
 *
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // Guests persist this token to recover their cart on later requests.
            'cart_token' => $this->session_id,
            'currency' => $this->currency,
            'item_count' => $this->whenLoaded('items', fn () => $this->items->sum('quantity')),
            'subtotal' => $this->whenLoaded('items', fn () => $this->subtotal()),
            'coupon_code' => $this->whenLoaded('coupon', fn () => $this->coupon?->code),
            'gift_card_code' => $this->whenLoaded('giftCard', fn () => $this->giftCard?->code),
            'items' => CartItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
