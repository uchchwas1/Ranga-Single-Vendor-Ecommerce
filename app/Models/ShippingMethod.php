<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A shipping method available at checkout.
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property string|null $carrier
 * @property string $min_order_amount
 * @property string|null $max_weight
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ShippingMethod extends Model
{
    /** @use HasFactory<\Database\Factories\ShippingMethodFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'carrier',
        'min_order_amount',
        'max_weight',
        'is_active',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_order_amount' => 'decimal:2',
            'max_weight' => 'decimal:3',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Rate rows for this method.
     *
     * @return HasMany<ShippingRate, $this>
     */
    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class);
    }

    /**
     * Scope to active methods, ordered for display.
     *
     * @param  Builder<ShippingMethod>  $query
     * @return Builder<ShippingMethod>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Use the code for route-model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
