<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Services\Support\CacheService;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of the category repository.
 */
class EloquentCategoryRepository implements CategoryRepositoryContract
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        private readonly CacheService $cache,
    ) {
    }

    /**
     * The active category tree (roots with nested children).
     *
     * Cached for the configured TTL; invalidated by the category observer.
     *
     * @return Collection<int, Category>
     */
    public function tree(): Collection
    {
        return $this->cache->remember(
            ['catalogue', 'categories'],
            'categories:tree',
            (int) config('ranga.cache.category_tree_ttl', 3600),
            static fn (): Collection => Category::query()
                ->active()
                ->root()
                ->with(['children' => fn ($q) => $q->active()->with('children')])
                ->orderBy('sort_order')
                ->get(),
        );
    }

    /**
     * Find an active category by slug.
     */
    public function findActiveBySlug(string $slug): ?Category
    {
        return Category::query()
            ->active()
            ->where('slug', $slug)
            ->first();
    }
}
