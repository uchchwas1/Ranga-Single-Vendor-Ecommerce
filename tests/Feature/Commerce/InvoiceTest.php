<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Events\Commerce\OrderPaid;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Invoice\Contracts\InvoiceRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Stub the PDF engine so the suite needs no dompdf rendering.
        $this->app->bind(InvoiceRenderer::class, fn (): InvoiceRenderer => new class implements InvoiceRenderer {
            public function render(Order $order, Invoice $invoice): string
            {
                return '%PDF-1.4 stub';
            }
        });

        Storage::fake('local');
    }

    public function test_a_customer_can_download_their_invoice(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        OrderItem::factory()->for($order)->create();

        Sanctum::actingAs($user);

        $response = $this->get("/api/v1/orders/{$order->order_number}/invoice");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));

        $invoice = Invoice::query()->where('order_id', $order->id)->firstOrFail();
        $this->assertNotNull($invoice->pdf_path);
        Storage::disk('local')->assertExists($invoice->pdf_path);
    }

    public function test_invoice_is_generated_when_an_order_is_paid(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->for($order)->create();

        event(new OrderPaid($order));

        $this->assertDatabaseHas('invoices', ['order_id' => $order->id]);
    }
}
