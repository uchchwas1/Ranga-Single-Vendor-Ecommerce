<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use App\Services\Support\CacheService;

/**
 * Invalidates catalogue caches when products change.
 */
class ProductObserver
{
    /**
     * Create a new observer instance.
     */
    public function __construct(
        private readonly CacheService $cache,
    ) {
    }

    /**
     * Handle the Product "saved" event.
     */
    public function saved(Product $product): void
    {
        $this->cache->flush(['catalogue', 'products']);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->cache->flush(['catalogue', 'products']);
    }
}
