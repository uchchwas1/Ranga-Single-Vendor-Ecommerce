<?php

declare(strict_types=1);

namespace App\Http\Resources\Cms;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a banner.
 *
 * @mixin Banner
 */
class BannerResource extends JsonResource
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
            'title' => $this->title,
            'image' => $this->image,
            'mobile_image' => $this->mobile_image,
            'link' => $this->link,
            'position' => $this->position->value,
            'sort_order' => $this->sort_order,
        ];
    }
}
