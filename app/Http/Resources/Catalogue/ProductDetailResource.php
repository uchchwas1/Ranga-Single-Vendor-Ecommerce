<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalogue;

use App\Models\Product;
use App\Models\ProductTag;
use App\Models\ProductVideo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full API representation of a product detail page.
 *
 * @mixin Product
 */
class ProductDetailResource extends JsonResource
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
            'barcode' => $this->barcode,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'specifications' => $this->specifications,
            'faqs' => $this->faqs,
            'status' => $this->status->value,
            'is_featured' => $this->is_featured,
            'is_digital' => $this->is_digital,
            'price_from' => $this->priceFrom(),
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'dimensions' => $this->dimensions,
            'video_url' => $this->video_url,
            'meta' => [
                'title' => $this->meta_title,
                'description' => $this->meta_description,
                'keywords' => $this->meta_keywords,
            ],
            'brand' => $this->whenLoaded('brand', fn () => $this->brand !== null ? new BrandResource($this->brand) : null),
            'category' => $this->whenLoaded('category', fn () => $this->category !== null ? new CategoryResource($this->category) : null),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(static fn ($image): array => [
                'path' => $image->image_path,
                'alt' => $image->alt_text,
                'is_primary' => $image->is_primary,
            ])->all()),
            'videos' => $this->whenLoaded('videos', fn () => $this->videos->map(static fn (ProductVideo $video): array => [
                'url' => $video->video_url,
                'thumbnail' => $video->thumbnail,
                'title' => $video->title,
            ])->all()),
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(static fn (ProductTag $tag): string => $tag->tag)->all()),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
