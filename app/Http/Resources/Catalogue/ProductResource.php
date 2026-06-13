<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalogue;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Compact API representation of a product for listings/search.
 *
 * @mixin Product
 */
class ProductResource extends JsonResource
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
            'sku' => $this->sku,
            'short_description' => $this->short_description,
            'price_from' => $this->priceForList(),
            'is_featured' => $this->is_featured,
            'image' => $this->whenLoaded('primaryImage', fn () => $this->primaryImage?->image_path),
            'brand' => $this->whenLoaded('brand', fn () => $this->brand !== null ? [
                'name' => $this->brand->name,
                'slug' => $this->brand->slug,
            ] : null),
            'category' => $this->whenLoaded('category', fn () => $this->category !== null ? [
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
        ];
    }

    /**
     * Lowest price, using the withMin alias when present, else a query.
     */
    private function priceForList(): ?float
    {
        /** @var mixed $aliased */
        $aliased = $this->getAttribute('price_from');

        if ($aliased !== null) {
            return (float) $aliased;
        }

        return $this->resource->priceFrom();
    }
}
