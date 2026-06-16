<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use App\Services\Support\CacheService;

/**
 * Invalidates the category-tree cache when categories change.
 */
class CategoryObserver
{
    /**
     * Create a new observer instance.
     */
    public function __construct(
        private readonly CacheService $cache,
    ) {
    }

    /**
     * Handle the Category "saved" event.
     */
    public function saved(Category $category): void
    {
        $this->cache->flush(['catalogue', 'categories']);
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->cache->flush(['catalogue', 'categories']);
    }
}
