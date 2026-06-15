<?php

declare(strict_types=1);

namespace Tests\Feature\Marketing;

use App\Models\Bundle;
use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketingBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_live_flash_sales(): void
    {
        $live = FlashSale::factory()->create();
        FlashSaleItem::factory()->for($live)->create(['product_id' => Product::factory()->create()->id]);

        FlashSale::factory()->expired()->create();

        $this->getJson('/api/v1/flash-sales/active')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $live->id);
    }

    public function test_it_lists_active_bundles_and_shows_one(): void
    {
        $bundle = Bundle::factory()->create(['slug' => 'eid-special']);

        $this->getJson('/api/v1/bundles')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/bundles/eid-special')
            ->assertOk()
            ->assertJsonPath('data.slug', 'eid-special');
    }

    public function test_unknown_bundle_returns_404(): void
    {
        $this->getJson('/api/v1/bundles/nope')->assertNotFound();
    }
}
