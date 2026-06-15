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

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_cancel_a_pending_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Pending]);

        $this->postJson("/api/v1/orders/{$order->order_number}/cancel", ['reason' => 'Changed my mind'])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertNotNull($order->fresh()?->cancelled_at);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_a_shipped_order_cannot_be_cancelled(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Shipped]);

        $this->postJson("/api/v1/orders/{$order->order_number}/cancel")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['order']);
    }

    public function test_a_customer_cannot_view_another_users_order(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $order = Order::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->getJson("/api/v1/orders/{$order->order_number}")->assertNotFound();
    }

    public function test_an_admin_can_change_order_status_and_record_the_timeline(): void
    {
        Permission::findOrCreate('orders.manage', 'web');
        $admin = User::factory()->create();
        $admin->givePermissionTo('orders.manage');
        Sanctum::actingAs($admin);

        $order = Order::factory()->create(['status' => OrderStatus::Pending]);

        $this->putJson("/api/v1/admin/orders/{$order->order_number}/status", [
            'status' => 'processing',
            'comment' => 'Packing now',
            'notify_customer' => true,
        ])->assertOk()->assertJsonPath('data.status', 'processing');

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'processing',
            'notify_customer' => true,
        ]);
    }

    public function test_admin_status_change_requires_permission(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $order = Order::factory()->create();

        $this->putJson("/api/v1/admin/orders/{$order->order_number}/status", ['status' => 'processing'])
            ->assertForbidden();
    }
}
