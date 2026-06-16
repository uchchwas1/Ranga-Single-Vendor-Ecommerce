<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Brand;
use App\Services\Support\CacheService;

/**
 * Invalidates catalogue caches when brands change.
 */
class BrandObserver
{
    /**
     * Create a new observer instance.
     */
    public function __construct(
        private readonly CacheService $cache,
    ) {
    }

    /**
     * Handle the Brand "saved" event.
     */
    public function saved(Brand $brand): void
    {
        $this->cache->flush(['catalogue']);
    }

    /**
     * Handle the Brand "deleted" event.
     */
    public function deleted(Brand $brand): void
    {
        $this->cache->flush(['catalogue']);
    }
}
