<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Seo\SchemaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_schema_includes_offers(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->for($product)->create(['price' => 1200, 'stock' => 3]);
        $product->load('variants', 'brand');

        $schema = (new SchemaService())->product($product);

        $this->assertSame('Product', $schema['@type']);
        $this->assertSame('1200.00', $schema['offers']['price']);
        $this->assertSame('https://schema.org/InStock', $schema['offers']['availability']);
    }

    public function test_organization_schema_is_built(): void
    {
        $schema = (new SchemaService())->organization();

        $this->assertSame('Organization', $schema['@type']);
        $this->assertArrayHasKey('name', $schema);
    }

    public function test_breadcrumb_positions_increment(): void
    {
        $schema = (new SchemaService())->breadcrumb([
            ['name' => 'Home', 'url' => 'https://x/'],
            ['name' => 'Sarees', 'url' => 'https://x/sarees'],
        ]);

        $this->assertSame(1, $schema['itemListElement'][0]['position']);
        $this->assertSame(2, $schema['itemListElement'][1]['position']);
    }
}
