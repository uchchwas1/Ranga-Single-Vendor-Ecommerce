<?php

declare(strict_types=1);

namespace App\Http\Resources\Cms;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a CMS page.
 *
 * @mixin Page
 */
class PageResource extends JsonResource
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
            'slug' => $this->slug,
            'content' => $this->content,
            'meta' => [
                'title' => $this->meta_title,
                'description' => $this->meta_description,
            ],
            'is_published' => $this->is_published,
        ];
    }
}
