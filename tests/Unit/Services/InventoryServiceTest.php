<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Events\Inventory\InventoryLow;
use App\Models\Inventory;
use App\Models\ProductVariant;
use App\Services\Catalogue\InventoryService;
use App\Support\Enums\InventoryLogType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): InventoryService
    {
        return app(InventoryService::class);
    }

    public function test_it_applies_an_outbound_movement_and_writes_a_log(): void
    {
        Event::fake([InventoryLow::class]);

        $variant = ProductVariant::factory()->create(['stock' => 50]);
        $inventory = Inventory::factory()->create([
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'quantity' => 50,
            'low_stock_threshold' => 5,
        ]);

        $result = $this->service()->adjust($inventory, InventoryLogType::Sale, -10, 'order #1');

        $this->assertSame(40, $result->quantity);
        $this->assertDatabaseHas('inventory_logs', [
            'inventory_id' => $inventory->id,
            'quantity_before' => 50,
            'quantity_after' => 40,
            'type' => InventoryLogType::Sale->value,
        ]);
        $this->assertSame(40, $variant->fresh()?->stock);
        Event::assertNotDispatched(InventoryLow::class);
    }

    public function test_it_never_drops_below_zero(): void
    {
        Event::fake([InventoryLow::class]);

        $inventory = Inventory::factory()->create(['quantity' => 3, 'low_stock_threshold' => 5]);

        $result = $this->service()->adjust($inventory, InventoryLogType::Sale, -10);

        $this->assertSame(0, $result->quantity);
    }

    public function test_it_raises_a_low_stock_event_when_threshold_crossed(): void
    {
        Event::fake([InventoryLow::class]);

        $inventory = Inventory::factory()->create(['quantity' => 20, 'low_stock_threshold' => 5]);

        $this->service()->adjust($inventory, InventoryLogType::Sale, -17);

        Event::assertDispatched(InventoryLow::class);
    }

    public function test_it_reserves_and_releases_stock(): void
    {
        $inventory = Inventory::factory()->create(['quantity' => 30, 'reserved_quantity' => 0]);

        $this->service()->reserve($inventory, 5);
        $this->assertSame(5, $inventory->fresh()?->reserved_quantity);

        $this->service()->release($inventory, 3);
        $this->assertSame(2, $inventory->fresh()?->reserved_quantity);
    }
}
