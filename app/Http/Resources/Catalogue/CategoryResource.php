<?php

declare(strict_types=1);

namespace App\Http\Resources\Catalogue;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a category, including any eager-loaded children.
 *
 * @mixin Category
 */
class CategoryResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image' => $this->image,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            'children' => $this->relationLoaded('children')
                ? self::collection($this->children)
                : [],
        ];
    }
}
