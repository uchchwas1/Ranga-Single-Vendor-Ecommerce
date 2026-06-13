<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Persistence boundary for Inventory aggregates.
 */
interface InventoryRepositoryContract
{
    /**
     * Find an inventory row by primary key.
     */
    public function find(string $id): ?Inventory;

    /**
     * Inventory rows at or below their low-stock threshold.
     *
     * @return Collection<int, Inventory>
     */
    public function lowStock(int $limit = 100): Collection;

    /**
     * Persist an inventory row's current quantities.
     */
    public function save(Inventory $inventory): Inventory;
}
