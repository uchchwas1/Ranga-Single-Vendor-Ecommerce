<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persistence boundary for Brand aggregates.
 */
interface BrandRepositoryContract
{
    /**
     * All active brands, ordered by name.
     *
     * @return Collection<int, Brand>
     */
    public function allActive(): Collection;

    /**
     * Find an active brand by slug.
     */
    public function findActiveBySlug(string $slug): ?Brand;
}
