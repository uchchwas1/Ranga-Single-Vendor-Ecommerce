<?php

declare(strict_types=1);

namespace App\Http\Resources\Commerce;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a cart line item.
 *
 * @mixin CartItem
 */
class CartItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'name' => $this->whenLoaded('product', fn () => $this->product?->name),
            'slug' => $this->whenLoaded('product', fn () => $this->product?->slug),
            'sku' => $this->whenLoaded('variant', fn () => $this->variant?->sku),
            'quantity' => $this->quantity,
            'unit_price' => $this->price_at_add,
            'line_total' => $this->lineTotal(),
        ];
    }
}
