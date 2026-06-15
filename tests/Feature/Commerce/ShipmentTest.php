<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Order;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ShipmentTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Permission::findOrCreate('orders.manage', 'web');
        $admin = User::factory()->create();
        $admin->givePermissionTo('orders.manage');

        return $admin;
    }

    public function test_an_admin_can_ship_an_order_and_advance_its_status(): void
    {
        $admin = $this->admin();
        Sanctum::actingAs($admin);
        $order = Order::factory()->create(['status' => OrderStatus::Confirmed]);

        $this->postJson("/api/v1/admin/orders/{$order->order_number}/shipments", [
            'tracking_number' => 'TRACK123',
            'carrier' => 'Pathao',
            'carrier_url' => 'https://track.example.com',
        ])
            ->assertCreated()
            ->assertJsonPath('data.tracking_number', 'TRACK123')
            ->assertJsonPath('data.tracking_url', 'https://track.example.com/TRACK123');

        $this->assertSame('shipped', $order->fresh()?->status->value);
        $this->assertSame('shipped', $order->fresh()?->shipping_status->value);
    }

    public function test_a_customer_can_track_their_order_shipments(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Confirmed]);

        Sanctum::actingAs($admin);
        $this->postJson("/api/v1/admin/orders/{$order->order_number}/shipments", [
            'tracking_number' => 'TRACK999',
            'carrier' => 'RedX',
        ])->assertCreated();

        Sanctum::actingAs($customer);
        $this->getJson("/api/v1/orders/{$order->order_number}/tracking")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tracking_number', 'TRACK999');
    }

    public function test_order_detail_includes_timeline_and_shipments(): void
    {
        $admin = $this->admin();
        $customer = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Confirmed]);

        Sanctum::actingAs($admin);
        $this->postJson("/api/v1/admin/orders/{$order->order_number}/shipments", ['carrier' => 'Pathao'])
            ->assertCreated();

        Sanctum::actingAs($customer);
        $this->getJson("/api/v1/orders/{$order->order_number}")
            ->assertOk()
            ->assertJsonStructure(['data' => ['timeline', 'shipments']])
            ->assertJsonPath('data.shipments.0.carrier', 'Pathao');
    }
}
