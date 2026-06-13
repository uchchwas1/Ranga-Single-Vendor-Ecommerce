<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Brand;
use App\Repositories\Contracts\BrandRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of the brand repository.
 */
class EloquentBrandRepository implements BrandRepositoryContract
{
    /**
     * All active brands, ordered by name.
     *
     * @return Collection<int, Brand>
     */
    public function allActive(): Collection
    {
        return Brand::query()
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Find an active brand by slug.
     */
    public function findActiveBySlug(string $slug): ?Brand
    {
        return Brand::query()
            ->active()
            ->where('slug', $slug)
            ->first();
    }
}
