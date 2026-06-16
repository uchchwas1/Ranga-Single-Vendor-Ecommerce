<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Services\Commerce\OrderManagementService;
use App\Support\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_order_status_change_notifies_the_customer_across_channels(): void
    {
        $user = User::factory()->create(['phone' => '01712345678']);
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Confirmed]);

        app(OrderManagementService::class)->changeStatus(
            $order,
            OrderStatus::Shipped,
            'On its way',
            notifyCustomer: true,
        );

        // Database notification stored, plus SMS + WhatsApp log rows written.
        $this->assertSame(1, $user->fresh()?->notifications()->count());
        $this->assertDatabaseHas('sms_logs', ['to' => '01712345678']);
        $this->assertDatabaseHas('whatsapp_logs', ['template' => 'order_status_update']);
    }

    public function test_a_user_can_list_and_read_notifications(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $user->notify(new \App\Notifications\OrderStatusNotification($order, OrderStatus::Processing));

        Sanctum::actingAs($user);

        $id = $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->json('data.0.id');

        $this->postJson("/api/v1/notifications/{$id}/read")->assertOk();
        $this->assertSame(0, $user->fresh()?->unreadNotifications()->count());
    }

    public function test_a_user_can_register_a_push_subscription(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/profile/push-subscriptions', [
            'endpoint' => 'https://push.example.com/abc123',
            'device_type' => 'web',
        ])->assertCreated();

        $this->assertDatabaseHas('push_subscriptions', ['endpoint' => 'https://push.example.com/abc123']);
    }
}
