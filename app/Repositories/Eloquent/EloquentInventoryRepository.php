<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

/**
 * Eloquent implementation of the inventory repository.
 */
class EloquentInventoryRepository implements InventoryRepositoryContract
{
    /**
     * Find an inventory row by primary key.
     */
    public function find(string $id): ?Inventory
    {
        return Inventory::query()->find($id);
    }

    /**
     * Inventory rows at or below their low-stock threshold.
     *
     * @return Collection<int, Inventory>
     */
    public function lowStock(int $limit = 100): Collection
    {
        return Inventory::query()
            ->lowStock()
            ->with(['product', 'variant', 'warehouse'])
            ->limit($limit)
            ->get();
    }

    /**
     * Persist an inventory row's current quantities.
     */
    public function save(Inventory $inventory): Inventory
    {
        $inventory->save();

        return $inventory;
    }
}
