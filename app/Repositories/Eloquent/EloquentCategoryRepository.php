<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of the category repository.
 */
class EloquentCategoryRepository implements CategoryRepositoryContract
{
    /**
     * The active category tree (roots with nested children).
     *
     * @return Collection<int, Category>
     */
    public function tree(): Collection
    {
        return Category::query()
            ->active()
            ->root()
            ->with(['children' => fn ($q) => $q->active()->with('children')])
            ->orderBy('sort_order')
            ->get();
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
