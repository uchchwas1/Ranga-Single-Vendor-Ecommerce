<?php

declare(strict_types=1);

namespace App\Services\Catalogue;

use App\Events\Inventory\InventoryLow;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Support\Enums\InventoryLogType;
use Illuminate\Support\Facades\DB;

/**
 * Application service for stock movements and low-stock detection.
 */
class InventoryService
{
    /**
     * Apply a signed stock movement, write an audit log, keep the
     * variant's denormalised stock in sync, and raise a low-stock
     * event when the threshold is crossed.
     *
     * @param  int  $delta  Signed change (positive = inbound, negative = outbound).
     */
    public function adjust(
        Inventory $inventory,
        InventoryLogType $type,
        int $delta,
        ?string $note = null,
        ?string $userId = null,
    ): Inventory {
        return DB::transaction(function () use ($inventory, $type, $delta, $note, $userId): Inventory {
            $before = $inventory->quantity;
            $after = max(0, $before + $delta);

            $inventory->quantity = $after;
            $inventory->save();

            InventoryLog::query()->create([
                'inventory_id' => $inventory->id,
                'type' => $type,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'note' => $note,
                'created_by' => $userId,
            ]);

            $this->syncVariantStock($inventory);

            if ($inventory->isLow()) {
                InventoryLow::dispatch($inventory);
            }

            return $inventory;
        });
    }

    /**
     * Reserve stock for a pending order (does not reduce on-hand quantity).
     */
    public function reserve(Inventory $inventory, int $quantity): Inventory
    {
        $inventory->reserved_quantity += max(0, $quantity);
        $inventory->save();

        return $inventory;
    }

    /**
     * Release a previous reservation.
     */
    public function release(Inventory $inventory, int $quantity): Inventory
    {
        $inventory->reserved_quantity = max(0, $inventory->reserved_quantity - max(0, $quantity));
        $inventory->save();

        return $inventory;
    }

    /**
     * Recompute the denormalised stock value on the related variant.
     */
    private function syncVariantStock(Inventory $inventory): void
    {
        $variant = $inventory->variant;

        if ($variant === null) {
            return;
        }

        $variant->stock = (int) $variant->inventory()->sum('quantity');
        $variant->save();
    }
}
