<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persistence boundary for Category aggregates.
 */
interface CategoryRepositoryContract
{
    /**
     * The active category tree (roots with nested children).
     *
     * @return Collection<int, Category>
     */
    public function tree(): Collection;

    /**
     * Find an active category by slug.
     */
    public function findActiveBySlug(string $slug): ?Category;
}
