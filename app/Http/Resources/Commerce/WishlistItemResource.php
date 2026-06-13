<?php

declare(strict_types=1);

namespace App\Http\Resources\Commerce;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a wishlist entry.
 *
 * @mixin Wishlist
 */
class WishlistItemResource extends JsonResource
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
            'image' => $this->whenLoaded('product', fn () => $this->product?->primaryImage?->image_path),
            'added_at' => $this->added_at?->toIso8601String(),
        ];
    }
}
