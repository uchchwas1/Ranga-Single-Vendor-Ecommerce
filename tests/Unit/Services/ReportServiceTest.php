<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Reports\ReportService;
use App\Support\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): ReportService
    {
        return app(ReportService::class);
    }

    public function test_sales_summary_excludes_cancelled_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Confirmed, 'total' => 1000]);
        Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Confirmed, 'total' => 2000]);
        Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Cancelled, 'total' => 9999]);

        $summary = $this->service()->salesSummary(Carbon::now()->subDay(), Carbon::now()->addDay());

        $this->assertSame(2, $summary['orders']);
        $this->assertEqualsWithDelta(3000.0, $summary['gross_sales'], 0.001);
        $this->assertEqualsWithDelta(1500.0, $summary['average_order_value'], 0.001);
    }

    public function test_top_products_ranks_by_quantity_sold(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Confirmed]);
        OrderItem::factory()->for($order)->create(['product_name' => 'Saree', 'quantity' => 5, 'subtotal' => 5000]);
        OrderItem::factory()->for($order)->create(['product_name' => 'Kurti', 'quantity' => 2, 'subtotal' => 2000]);

        $top = $this->service()->topProducts(Carbon::now()->subDay(), Carbon::now()->addDay());

        $this->assertSame('Saree', $top[0]['name']);
        $this->assertSame(5, $top[0]['quantity']);
    }
}
