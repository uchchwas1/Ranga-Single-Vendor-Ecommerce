<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\Bundle;
use Illuminate\Database\Eloquent\Collection;

/**
 * Application service for product bundles.
 */
class BundleService
{
    /**
     * Active bundles with their items.
     *
     * @return Collection<int, Bundle>
     */
    public function active(): Collection
    {
        return Bundle::query()
            ->active()
            ->with(['items.product.primaryImage'])
            ->get();
    }

    /**
     * Find an active bundle by slug.
     */
    public function findBySlug(string $slug): ?Bundle
    {
        return Bundle::query()->active()->where('slug', $slug)->with('items.product')->first();
    }
}
