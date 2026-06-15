<?php

declare(strict_types=1);

namespace App\Http\Resources\Marketing;

use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a live flash sale.
 *
 * @mixin FlashSale
 */
class FlashSaleResource extends JsonResource
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
            'name' => $this->name,
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(static fn (FlashSaleItem $item): array => [
                'product_name' => $item->product?->name,
                'product_slug' => $item->product?->slug,
                'image' => $item->product?->primaryImage?->image_path,
                'sale_price' => $item->sale_price,
                'remaining' => $item->quantity_limit !== null ? max(0, $item->quantity_limit - $item->sold_count) : null,
            ])->all()),
        ];
    }
}
