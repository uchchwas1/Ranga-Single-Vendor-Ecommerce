<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Order;
use App\Models\Payment;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\PaymentGateway;
use App\Support\Enums\PaymentStatus;
use App\Support\Enums\ReturnStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReturnRefundTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Permission::findOrCreate('orders.manage', 'web');
        $admin = User::factory()->create();
        $admin->givePermissionTo('orders.manage');

        return $admin;
    }

    public function test_a_customer_can_submit_a_return_on_a_delivered_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Delivered]);

        $this->postJson("/api/v1/orders/{$order->order_number}/return", [
            'reason' => 'Wrong size',
            'description' => 'Need a larger size',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('return_requests', ['order_id' => $order->id, 'reason' => 'Wrong size']);
    }

    public function test_a_return_cannot_be_submitted_on_a_pending_order(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Pending]);

        $this->postJson("/api/v1/orders/{$order->order_number}/return", ['reason' => 'Wrong size'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['order']);
    }

    public function test_an_admin_can_approve_a_return_and_issue_a_gateway_refund(): void
    {
        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response(['APIConnect' => 'DONE']),
        ]);

        $customer = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $customer->id,
            'status' => OrderStatus::Delivered,
            'payment_status' => PaymentStatus::Paid,
            'total' => 2060,
        ]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'gateway' => PaymentGateway::Sslcommerz,
            'status' => PaymentStatus::Paid,
            'gateway_transaction_id' => 'bank_txn_1',
            'amount' => 2060,
        ]);
        $return = ReturnRequest::factory()->create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'status' => ReturnStatus::Pending,
        ]);

        Sanctum::actingAs($this->admin());

        $this->postJson("/api/v1/admin/returns/{$return->id}/approve", [
            'refund_method' => 'original_payment',
            'admin_note' => 'Approved',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('refund.status', 'completed');

        $this->assertSame('refunded', $payment->fresh()?->status->value);
        $this->assertSame('refunded', $order->fresh()?->payment_status->value);
    }

    public function test_an_admin_can_reject_a_return(): void
    {
        $return = ReturnRequest::factory()->create(['status' => ReturnStatus::Pending]);

        Sanctum::actingAs($this->admin());

        $this->postJson("/api/v1/admin/returns/{$return->id}/reject", ['admin_note' => 'Outside window'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected');
    }
}
