<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A product/variant offered at a flash-sale price.
 *
 * @property string $id
 * @property string $flash_sale_id
 * @property string $product_id
 * @property string|null $variant_id
 * @property string $sale_price
 * @property int|null $quantity_limit
 * @property int $sold_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class FlashSaleItem extends Model
{
    /** @use HasFactory<\Database\Factories\FlashSaleItemFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'flash_sale_id',
        'product_id',
        'variant_id',
        'sale_price',
        'quantity_limit',
        'sold_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_price' => 'decimal:2',
            'quantity_limit' => 'integer',
            'sold_count' => 'integer',
        ];
    }

    /**
     * The flash sale this item belongs to.
     *
     * @return BelongsTo<FlashSale, $this>
     */
    public function flashSale(): BelongsTo
    {
        return $this->belongsTo(FlashSale::class);
    }

    /**
     * The discounted product.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Whether sale stock remains.
     */
    public function hasStock(): bool
    {
        return $this->quantity_limit === null || $this->sold_count < $this->quantity_limit;
    }
}
