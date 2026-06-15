<?php

declare(strict_types=1);

namespace App\Services\Reports;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use Illuminate\Support\Carbon;

/**
 * Aggregated reporting for the admin dashboard and report screens.
 *
 * Uses Eloquent/query-builder aggregates only — no raw SQL.
 */
class ReportService
{
    /**
     * Sales summary for a date range (cancelled orders excluded).
     *
     * @return array{orders: int, gross_sales: float, items_sold: int, average_order_value: float}
     */
    public function salesSummary(Carbon $from, Carbon $to): array
    {
        $base = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', OrderStatus::Cancelled->value);

        $orders = (clone $base)->count();
        $gross = (float) (clone $base)->sum('total');
        $orderIds = (clone $base)->pluck('id')->all();
        $itemsSold = (int) OrderItem::query()->whereIn('order_id', $orderIds)->sum('quantity');

        return [
            'orders' => $orders,
            'gross_sales' => round($gross, 2),
            'items_sold' => $itemsSold,
            'average_order_value' => $orders > 0 ? round($gross / $orders, 2) : 0.0,
        ];
    }

    /**
     * Best-selling products by quantity within a range.
     *
     * @return list<array{product_id: string|null, name: string, quantity: int, revenue: float}>
     */
    public function topProducts(Carbon $from, Carbon $to, int $limit = 5): array
    {
        $orderIds = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->where('status', '!=', OrderStatus::Cancelled->value)
            ->pluck('id')
            ->all();

        return OrderItem::query()
            ->whereIn('order_id', $orderIds)
            ->get(['product_id', 'product_name', 'quantity', 'subtotal'])
            ->groupBy('product_name')
            ->map(static fn ($rows): array => [
                'product_id' => $rows->first()?->product_id,
                'name' => (string) $rows->first()?->product_name,
                'quantity' => (int) $rows->sum('quantity'),
                'revenue' => round((float) $rows->sum(static fn (OrderItem $i): float => (float) $i->subtotal), 2),
            ])
            ->sortByDesc('quantity')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Customer base summary.
     *
     * @return array{total: int, new_this_month: int, with_orders: int}
     */
    public function customerSummary(): array
    {
        return [
            'total' => User::query()->count(),
            'new_this_month' => User::query()->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            'with_orders' => User::query()->whereHas('orders')->count(),
        ];
    }

    /**
     * Inventory health summary.
     *
     * @return array{products: int, low_stock_rows: int, units_on_hand: int}
     */
    public function inventorySummary(): array
    {
        return [
            'products' => Product::query()->count(),
            'low_stock_rows' => Inventory::query()->lowStock()->count(),
            'units_on_hand' => (int) Inventory::query()->sum('quantity'),
        ];
    }

    /**
     * Headline KPIs for the admin dashboard (last 30 days).
     *
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $sales = $this->salesSummary(Carbon::now()->subDays(30), Carbon::now());

        return [
            'sales_30d' => $sales,
            'customers' => $this->customerSummary(),
            'inventory' => $this->inventorySummary(),
            'top_products' => $this->topProducts(Carbon::now()->subDays(30), Carbon::now()),
        ];
    }
}
