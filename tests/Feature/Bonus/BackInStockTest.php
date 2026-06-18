<?php

declare(strict_types=1);

namespace Tests\Feature\Bonus;

use App\Models\BackInStockSubscription;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Notifications\BackInStockNotification;
use App\Services\Catalogue\InventoryService;
use App\Support\Enums\InventoryLogType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BackInStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_register_a_back_in_stock_watch(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->outOfStock()->create();

        $this->postJson('/api/v1/back-in-stock', [
            'variant_id' => $variant->id,
            'email' => 'shopper@example.com',
        ])->assertCreated();

        $this->assertDatabaseHas('back_in_stock_subscriptions', [
            'variant_id' => $variant->id,
            'email' => 'shopper@example.com',
        ]);
    }

    public function test_restocking_notifies_watchers(): void
    {
        Notification::fake();

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['stock' => 0]);
        $warehouse = Warehouse::factory()->create();
        $inventory = Inventory::factory()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
        ]);
        $watch = BackInStockSubscription::factory()->create([
            'variant_id' => $variant->id,
            'email' => 'waiting@example.com',
        ]);

        // Receive new stock — should trigger the restock notification.
        app(InventoryService::class)->adjust($inventory, InventoryLogType::Purchase, 10);

        Notification::assertSentOnDemand(BackInStockNotification::class);
        $this->assertNotNull($watch->fresh()?->notified_at);
    }
}
