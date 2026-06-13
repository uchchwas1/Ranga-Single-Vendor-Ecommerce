<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A purchasable variant of a product (a specific SKU).
 *
 * @property string $id
 * @property string $product_id
 * @property string $sku
 * @property string|null $barcode
 * @property string $price
 * @property string|null $compare_price
 * @property string|null $cost_price
 * @property int $stock
 * @property float|null $weight
 * @property string|null $image_id
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ProductVariant extends Model
{
    /** @use HasFactory<\Database\Factories\ProductVariantFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'price',
        'compare_price',
        'cost_price',
        'stock',
        'weight',
        'image_id',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock' => 'integer',
            'weight' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The owning product.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The image representing this variant.
     *
     * @return BelongsTo<ProductImage, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(ProductImage::class, 'image_id');
    }

    /**
     * Inventory rows for this variant across warehouses.
     *
     * @return HasMany<Inventory, $this>
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'variant_id');
    }

    /**
     * Attribute values that define this variant (e.g. Red / XL).
     *
     * @return BelongsToMany<AttributeValue, $this>
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'product_variant_attributes',
            'variant_id',
            'attribute_value_id',
        )->using(ProductVariantAttribute::class)->withPivot('attribute_id');
    }

    /**
     * Scope the query to active variants.
     *
     * @param  Builder<ProductVariant>  $query
     * @return Builder<ProductVariant>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Whether the variant currently has sellable stock.
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
}
