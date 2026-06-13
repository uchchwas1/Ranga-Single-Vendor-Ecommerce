<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_add_an_item_and_receive_a_cart_token(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1500, 'stock' => 10]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.item_count', 2)
            ->assertJsonStructure(['data' => ['id', 'cart_token', 'items' => [['id', 'quantity', 'line_total']]]]);

        $this->assertNotNull($response->json('data.cart_token'));
    }

    public function test_an_authenticated_user_cart_persists_across_requests(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 5]);

        $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertCreated();

        $this->getJson('/api/v1/cart')
            ->assertOk()
            ->assertJsonPath('data.item_count', 1);
    }

    public function test_adding_the_same_line_twice_increments_quantity(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 10]);

        $payload = ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 1];
        $this->postJson('/api/v1/cart/items', $payload)->assertCreated();
        $this->postJson('/api/v1/cart/items', $payload)
            ->assertCreated()
            ->assertJsonPath('data.item_count', 2);
    }

    public function test_it_rejects_quantities_beyond_available_stock(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 1]);

        $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 5,
        ])->assertUnprocessable()->assertJsonValidationErrors(['quantity']);
    }

    public function test_a_user_can_update_and_remove_a_cart_line(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 10]);

        $itemId = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->json('data.items.0.id');

        $this->putJson("/api/v1/cart/items/{$itemId}", ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('data.item_count', 3);

        $this->deleteJson("/api/v1/cart/items/{$itemId}")
            ->assertOk()
            ->assertJsonPath('data.item_count', 0);
    }

    public function test_a_user_can_save_an_item_for_later(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 10]);

        $itemId = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->json('data.items.0.id');

        $this->postJson("/api/v1/cart/items/{$itemId}/save-for-later")
            ->assertOk()
            ->assertJsonPath('data.item_count', 0);

        $this->assertDatabaseHas('cart_item_saved', ['product_id' => $product->id]);
    }

    public function test_save_for_later_requires_authentication(): void
    {
        $product = Product::factory()->create();
        $variant = ProductVariant::factory()->for($product)->create(['price' => 1000, 'stock' => 10]);

        $itemId = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->json('data.items.0.id');

        $token = $this->getJson('/api/v1/cart')->json('data.cart_token');

        $this->withHeaders(['X-Cart-Token' => $token])
            ->postJson("/api/v1/cart/items/{$itemId}/save-for-later")
            ->assertUnauthorized();
    }
}
