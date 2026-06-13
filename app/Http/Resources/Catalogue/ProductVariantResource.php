<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalogue;

use App\Models\AttributeValue;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a product variant.
 *
 * @mixin ProductVariant
 */
class ProductVariantResource extends JsonResource
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
            'sku' => $this->sku,
            'price' => $this->price,
            'compare_price' => $this->compare_price,
            'stock' => $this->stock,
            'in_stock' => $this->isInStock(),
            'image' => $this->whenLoaded('image', fn () => $this->image?->image_path),
            'attributes' => $this->whenLoaded(
                'attributeValues',
                fn () => $this->attributeValues->map(static fn (AttributeValue $value): array => [
                    'attribute_id' => $value->attribute_id,
                    'attribute' => $value->relationLoaded('attribute') ? $value->attribute->name : null,
                    'value' => $value->value,
                    'hex' => $value->hex(),
                ])->all(),
            ),
        ];
    }
}
