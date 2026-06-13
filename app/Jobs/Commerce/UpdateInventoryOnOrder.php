<?php

declare(strict_types=1);

namespace App\Jobs\Commerce;

use App\Models\Inventory;
use App\Models\Order;
use App\Services\Catalogue\InventoryService;
use App\Support\Enums\InventoryLogType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Decrements warehouse stock for each line of a placed order.
 */
class UpdateInventoryOnOrder implements ShouldQueue
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
     * @param  string  $orderId  ULID of the placed order.
     */
    public function __construct(
        public readonly string $orderId,
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(InventoryService $inventory): void
    {
        $order = Order::query()->with('items')->find($this->orderId);

        if ($order === null) {
            return;
        }

        foreach ($order->items as $item) {
            if ($item->variant_id === null) {
                continue;
            }

            $row = Inventory::query()
                ->where('variant_id', $item->variant_id)
                ->orderByDesc('quantity')
                ->first();

            if ($row !== null) {
                $inventory->adjust(
                    $row,
                    InventoryLogType::Sale,
                    -$item->quantity,
                    note: 'Order '.$order->order_number,
                    userId: $order->user_id,
                );
            }
        }
    }
}
