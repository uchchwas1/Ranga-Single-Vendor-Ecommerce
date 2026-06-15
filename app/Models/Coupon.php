<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\CouponType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;

/**
 * A discount coupon.
 *
 * @property string $id
 * @property string $code
 * @property CouponType $type
 * @property string $value
 * @property string $min_order_amount
 * @property string|null $max_discount_amount
 * @property int|null $usage_limit
 * @property int $used_count
 * @property int|null $user_limit
 * @property list<string>|null $product_ids
 * @property list<string>|null $category_ids
 * @property \Illuminate\Support\Carbon|null $starts_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Coupon extends Model
{
    /** @use HasFactory<\Database\Factories\CouponFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'user_limit',
        'product_ids',
        'category_ids',
        'starts_at',
        'expires_at',
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
            'type' => CouponType::class,
            'value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'user_limit' => 'integer',
            'product_ids' => 'array',
            'category_ids' => 'array',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Usage records for this coupon.
     *
     * @return HasMany<CouponUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Whether the coupon is active and within its validity window/limit.
     */
    public function isRedeemable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = Date::now();

        if ($this->starts_at !== null && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at !== null && $now->gt($this->expires_at)) {
            return false;
        }

        return $this->usage_limit === null || $this->used_count < $this->usage_limit;
    }

    /**
     * Compute the monetary discount for a given subtotal.
     */
    public function discountFor(float $subtotal): float
    {
        $discount = match ($this->type) {
            CouponType::Percent => $subtotal * ((float) $this->value / 100),
            CouponType::Fixed => (float) $this->value,
            CouponType::FreeShipping => 0.0,
        };

        if ($this->max_discount_amount !== null) {
            $discount = min($discount, (float) $this->max_discount_amount);
        }

        return round(min($discount, $subtotal), 2);
    }

    /**
     * Scope the query to active coupons.
     *
     * @param  Builder<Coupon>  $query
     * @return Builder<Coupon>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Use the code for route-model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
