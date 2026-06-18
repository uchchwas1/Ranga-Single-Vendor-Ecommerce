<?php

declare(strict_types=1);

namespace Tests\Feature\Bonus;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Media\Contracts\QrGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Stub the QR engine so the suite doesn't depend on the renderer.
        $this->app->bind(QrGenerator::class, fn (): QrGenerator => new class implements QrGenerator {
            public function png(string $data, int $size = 300): string
            {
                return 'PNG-STUB';
            }
        });
    }

    public function test_it_streams_a_qr_png_for_a_product(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create();

        $this->get("/api/v1/products/{$product->slug}/qr")
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_it_returns_a_whatsapp_share_link(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create();

        $url = $this->getJson("/api/v1/products/{$product->slug}/share")
            ->assertOk()
            ->json('whatsapp');

        $this->assertStringStartsWith('https://wa.me/?text=', (string) $url);
    }
}
