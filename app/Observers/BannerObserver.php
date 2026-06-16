<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Banner;
use App\Services\Support\CacheService;

/**
 * Invalidates the homepage cache when banners change.
 */
class BannerObserver
{
    /**
     * Create a new observer instance.
     */
    public function __construct(
        private readonly CacheService $cache,
    ) {
    }

    /**
     * Handle the Banner "saved" event.
     */
    public function saved(Banner $banner): void
    {
        $this->cache->flush(['homepage']);
    }

    /**
     * Handle the Banner "deleted" event.
     */
    public function deleted(Banner $banner): void
    {
        $this->cache->flush(['homepage']);
    }
}
