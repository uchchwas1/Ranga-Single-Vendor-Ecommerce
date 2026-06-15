<?php

declare(strict_types=1);

namespace App\Http\Resources\Cms;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a navigation menu with nested items.
 *
 * @mixin Menu
 */
class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'location' => $this->location,
            'items' => $this->whenLoaded('items', fn () => $this->items->map($this->mapItem(...))->all()),
        ];
    }

    /**
     * Map a menu item (and its children) to an array.
     *
     * @return array<string, mixed>
     */
    private function mapItem(MenuItem $item): array
    {
        return [
            'label' => $item->label,
            'url' => $item->url,
            'target' => $item->target,
            'icon' => $item->icon,
            'children' => $item->relationLoaded('children')
                ? $item->children->map($this->mapItem(...))->all()
                : [],
        ];
    }
}
