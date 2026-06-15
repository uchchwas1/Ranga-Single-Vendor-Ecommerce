<?php

declare(strict_types=1);

namespace App\Services\Cms;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Read-side service for the storefront blog.
 */
class BlogService
{
    /**
     * Paginated published posts, optionally filtered by category.
     *
     * @return LengthAwarePaginator<int, BlogPost>
     */
    public function publishedPosts(?string $categorySlug = null, int $perPage = 12): LengthAwarePaginator
    {
        return BlogPost::query()
            ->published()
            ->with(['category', 'author'])
            ->when(
                $categorySlug !== null,
                fn ($query) => $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug)),
            )
            ->latest('published_at')
            ->paginate($perPage);
    }

    /**
     * Find a published post by slug and record a view.
     */
    public function findAndView(string $slug): ?BlogPost
    {
        $post = BlogPost::query()->published()->with(['category', 'author'])->where('slug', $slug)->first();

        $post?->increment('view_count');

        return $post;
    }
}
