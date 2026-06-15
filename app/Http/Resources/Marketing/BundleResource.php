<?php

declare(strict_types=1);

namespace App\Http\Resources\Marketing;

use App\Models\Bundle;
use App\Models\BundleItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a product bundle.
 *
 * @mixin Bundle
 */
class BundleResource extends JsonResource
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
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'discount_percent' => $this->discount_percent,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(static fn (BundleItem $item): array => [
                'product_name' => $item->product?->name,
                'product_slug' => $item->product?->slug,
                'quantity' => $item->quantity,
            ])->all()),
        ];
    }
}
