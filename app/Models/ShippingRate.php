<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A rate for a shipping method (optionally scoped to a zone).
 *
 * @property string $id
 * @property string $shipping_method_id
 * @property string|null $shipping_zone_id
 * @property string $base_rate
 * @property string $per_kg_rate
 * @property string|null $free_above_amount
 * @property int $estimated_days_min
 * @property int $estimated_days_max
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ShippingRate extends Model
{
    /** @use HasFactory<\Database\Factories\ShippingRateFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'shipping_method_id',
        'shipping_zone_id',
        'base_rate',
        'per_kg_rate',
        'free_above_amount',
        'estimated_days_min',
        'estimated_days_max',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_rate' => 'decimal:2',
            'per_kg_rate' => 'decimal:2',
            'free_above_amount' => 'decimal:2',
            'estimated_days_min' => 'integer',
            'estimated_days_max' => 'integer',
        ];
    }

    /**
     * The owning shipping method.
     *
     * @return BelongsTo<ShippingMethod, $this>
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    /**
     * Compute the shipping cost for a given order subtotal and weight.
     */
    public function costFor(float $subtotal, float $weightKg = 0.0): float
    {
        if ($this->free_above_amount !== null && $subtotal >= (float) $this->free_above_amount) {
            return 0.0;
        }

        return (float) $this->base_rate + ((float) $this->per_kg_rate * $weightKg);
    }
}
