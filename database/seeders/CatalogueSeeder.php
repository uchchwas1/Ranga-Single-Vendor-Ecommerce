<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttribute;
use App\Models\Warehouse;
use App\Support\Enums\AttributeType;
use App\Support\Enums\ProductStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * Seeds a representative catalogue: categories, brands, attributes,
 * products with variants, images and warehouse inventory.
 */
class CatalogueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Search indexing is skipped so seeding does not require a running
     * Meilisearch instance; run `php artisan scout:import` afterwards.
     */
    public function run(): void
    {
        Product::withoutSyncingToSearch(function (): void {
            $warehouse = Warehouse::factory()->create(['name' => 'Dhaka Central Warehouse']);

            $colour = Attribute::factory()->color()->create();
            $size = Attribute::factory()->size()->create();

            $colours = collect(['Red' => '#e11d48', 'Black' => '#111827', 'Green' => '#16a34a'])
                ->map(fn (string $hex, string $name): AttributeValue => AttributeValue::factory()
                    ->for($colour, 'attribute')
                    ->color($name, $hex)
                    ->create());

            $sizes = collect(['S', 'M', 'L', 'XL'])
                ->map(fn (string $value): AttributeValue => AttributeValue::factory()
                    ->for($size, 'attribute')
                    ->create(['value' => $value]));

            $categories = collect(['Sarees', 'Kurtis', 'Dresses', 'Accessories'])
                ->map(fn (string $name): Category => Category::factory()->create([
                    'name' => $name,
                    'slug' => Str::slug($name),
                ]));

            $brands = collect(['Ranga Signature', 'Deshi Threads', 'Colour Story'])
                ->map(fn (string $name): Brand => Brand::factory()->create([
                    'name' => $name,
                    'slug' => Str::slug($name),
                ]));

            $categories->each(function (Category $category) use ($brands, $warehouse, $colours, $sizes): void {
                Product::factory()
                    ->count(5)
                    ->state(fn (): array => [
                        'category_id' => $category->id,
                        'brand_id' => $brands->random()->id,
                        'status' => ProductStatus::Active,
                        'published_at' => Date::now()->subDays(2),
                    ])
                    ->create()
                    ->each(function (Product $product) use ($warehouse, $colours, $sizes): void {
                        ProductImage::factory()->for($product)->primary()->create();

                        ProductVariant::factory()
                            ->count(3)
                            ->for($product)
                            ->create()
                            ->each(function (ProductVariant $variant) use ($product, $warehouse, $colours, $sizes): void {
                                $colourValue = $colours->random();
                                $sizeValue = $sizes->random();

                                ProductVariantAttribute::create([
                                    'variant_id' => $variant->id,
                                    'attribute_id' => $colourValue->attribute_id,
                                    'attribute_value_id' => $colourValue->id,
                                ]);

                                ProductVariantAttribute::create([
                                    'variant_id' => $variant->id,
                                    'attribute_id' => $sizeValue->attribute_id,
                                    'attribute_value_id' => $sizeValue->id,
                                ]);

                                Inventory::factory()->create([
                                    'product_id' => $product->id,
                                    'variant_id' => $variant->id,
                                    'warehouse_id' => $warehouse->id,
                                    'quantity' => $variant->stock,
                                ]);
                            });
                    });
            });
        });
    }
}
