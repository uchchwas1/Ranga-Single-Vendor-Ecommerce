<?php

declare(strict_types=1);

namespace Tests\Feature\Commerce;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompareTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_build_a_comparison_list_via_token(): void
    {
        $a = Product::factory()->create();
        $b = Product::factory()->create();

        $token = (string) \Illuminate\Support\Str::ulid();
        $headers = ['X-Compare-Token' => $token];

        $this->withHeaders($headers)->postJson("/api/v1/products/compare/{$a->slug}")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->withHeaders($headers)->postJson("/api/v1/products/compare/{$b->slug}")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->withHeaders($headers)->getJson('/api/v1/products/compare')
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $this->withHeaders($headers)->deleteJson("/api/v1/products/compare/{$a->slug}")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_compare_does_not_capture_the_slug_route(): void
    {
        // Ensures /products/compare resolves to the compare endpoint,
        // not /products/{slug} with slug "compare".
        $this->getJson('/api/v1/products/compare')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
