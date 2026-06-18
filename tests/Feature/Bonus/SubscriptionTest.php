<?php

declare(strict_types=1);

namespace Tests\Feature\Bonus;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Subscription;
use App\Models\User;
use App\Support\Enums\SubscriptionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_create_and_manage_a_subscription(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1200]);

        $id = $this->postJson('/api/v1/subscriptions', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'interval' => 'monthly',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'active')
            ->json('data.id');

        $this->postJson("/api/v1/subscriptions/{$id}/pause")->assertOk()->assertJsonPath('data.status', 'paused');
        $this->postJson("/api/v1/subscriptions/{$id}/resume")->assertOk()->assertJsonPath('data.status', 'active');
        $this->deleteJson("/api/v1/subscriptions/{$id}")->assertOk()->assertJsonPath('data.status', 'cancelled');
    }

    public function test_a_user_cannot_manage_another_users_subscription(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $other = Subscription::factory()->create();

        $this->postJson("/api/v1/subscriptions/{$other->id}/pause")->assertNotFound();
    }

    public function test_the_renew_command_advances_due_subscriptions(): void
    {
        $subscription = Subscription::factory()->due()->create();
        $originalDate = $subscription->next_billing_at;

        $this->assertSame(0, $this->artisan('ranga:renew-subscriptions'));

        $this->assertTrue($subscription->fresh()?->next_billing_at?->greaterThan($originalDate));
        $this->assertSame(SubscriptionStatus::Active->value, $subscription->fresh()?->status->value);
    }

    public function test_preorder_products_can_be_added_beyond_stock(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create(['is_preorder' => true]);
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 0]);

        $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ])->assertCreated()->assertJsonPath('data.item_count', 3);
    }
}
