<?php

declare(strict_types=1);

namespace App\Jobs\Inventory;

use App\Models\Inventory;
use App\Models\StockAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;

/**
 * Records a low-stock alert and notifies operations staff.
 *
 * Idempotent within a 24h window so repeated movements on the same
 * low row do not spam alerts.
 */
class SendLowStockAlert implements ShouldQueue
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The backoff (seconds) between retries.
     *
     * @var list<int>
     */
    public array $backoff = [30, 120, 300];

    /**
     * @param  string  $inventoryId  ULID of the low inventory row.
     */
    public function __construct(
        public readonly string $inventoryId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $inventory = Inventory::query()->with(['product', 'variant', 'warehouse'])->find($this->inventoryId);

        if ($inventory === null || ! $inventory->isLow()) {
            return;
        }

        $recentlyNotified = $inventory->alerts()
            ->where('notified_at', '>=', Date::now()->subDay())
            ->exists();

        if ($recentlyNotified) {
            return;
        }

        StockAlert::query()->create([
            'inventory_id' => $inventory->id,
            'notified_at' => Date::now(),
        ]);

        Log::warning('Low stock alert', [
            'inventory_id' => $inventory->id,
            'product' => $inventory->product?->name,
            'variant_sku' => $inventory->variant?->sku,
            'warehouse' => $inventory->warehouse?->name,
            'available' => $inventory->availableQuantity(),
            'threshold' => $inventory->low_stock_threshold,
        ]);
    }
}
