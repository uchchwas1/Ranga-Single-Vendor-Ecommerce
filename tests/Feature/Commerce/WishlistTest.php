<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_add_list_and_remove_wishlist_items(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();

        $this->postJson("/api/v1/profile/wishlist/{$product->slug}")->assertCreated();

        $this->getJson('/api/v1/profile/wishlist')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $product->slug);

        $this->deleteJson("/api/v1/profile/wishlist/{$product->slug}")->assertOk();

        $this->getJson('/api/v1/profile/wishlist')->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_adding_the_same_product_twice_is_idempotent(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $product = Product::factory()->create();

        $this->postJson("/api/v1/profile/wishlist/{$product->slug}")->assertCreated();
        $this->postJson("/api/v1/profile/wishlist/{$product->slug}")->assertCreated();

        $this->getJson('/api/v1/profile/wishlist')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_wishlist_requires_authentication(): void
    {
        $this->getJson('/api/v1/profile/wishlist')->assertUnauthorized();
    }
}
