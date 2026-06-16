<?php

declare(strict_types=1);

namespace App\Http\Resources\Cms;

use App\Models\BlogPost;
use App\Services\Seo\SchemaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a blog post.
 *
 * @mixin BlogPost
 */
class BlogPostResource extends JsonResource
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
            'excerpt' => $this->excerpt,
            'content' => $this->when($request->routeIs('*.show'), fn () => $this->content),
            'featured_image' => $this->featured_image,
            'category' => $this->whenLoaded('category', fn () => $this->category?->name),
            'author' => $this->whenLoaded('author', fn () => $this->author?->name),
            'published_at' => $this->published_at?->toIso8601String(),
            'view_count' => $this->view_count,
            'structured_data' => $this->when(
                $request->routeIs('*.show'),
                fn (): array => app(SchemaService::class)->blogPosting($this->resource),
            ),
        ];
    }
}
