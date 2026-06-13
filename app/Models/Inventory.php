<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Stock for a product/variant at a given warehouse.
 *
 * @property string $id
 * @property string $product_id
 * @property string|null $variant_id
 * @property string $warehouse_id
 * @property int $quantity
 * @property int $reserved_quantity
 * @property int $low_stock_threshold
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Inventory extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryFactory> */
    use HasFactory, HasUlids;

    /**
     * The database table (singular, per the schema).
     *
     * @var string
     */
    protected $table = 'inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'low_stock_threshold',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'low_stock_threshold' => 'integer',
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
     * The variant this stock belongs to, if any.
     *
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * The warehouse holding the stock.
     *
     * @return BelongsTo<Warehouse, $this>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Movement history for this stock row.
     *
     * @return HasMany<InventoryLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * Stock-alert records raised for this row.
     *
     * @return HasMany<StockAlert, $this>
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    /**
     * Quantity available to sell (on-hand minus reserved).
     */
    public function availableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Whether available stock is at or below the low-stock threshold.
     */
    public function isLow(): bool
    {
        return $this->availableQuantity() <= $this->low_stock_threshold;
    }

    /**
     * Scope the query to rows at or below their low-stock threshold.
     *
     * @param  Builder<Inventory>  $query
     * @return Builder<Inventory>
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'low_stock_threshold');
    }
}
