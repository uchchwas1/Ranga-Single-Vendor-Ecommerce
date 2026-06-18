<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\SubscriptionInterval;
use App\Support\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

/**
 * A recurring subscription to a product.
 *
 * @property string $id
 * @property string $user_id
 * @property string $product_id
 * @property string|null $variant_id
 * @property SubscriptionInterval $interval
 * @property SubscriptionStatus $status
 * @property string $price
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $next_billing_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Subscription extends Model
{
    /** @use HasFactory<\Database\Factories\SubscriptionFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'product_id',
        'variant_id',
        'interval',
        'status',
        'price',
        'quantity',
        'next_billing_at',
        'started_at',
        'cancelled_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'interval' => SubscriptionInterval::class,
            'status' => SubscriptionStatus::class,
            'price' => 'decimal:2',
            'quantity' => 'integer',
            'next_billing_at' => 'datetime',
            'started_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * The subscribing user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The subscribed product.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The subscribed variant, if any.
     *
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Scope to active subscriptions due for renewal.
     *
     * @param  Builder<Subscription>  $query
     * @return Builder<Subscription>
     */
    public function scopeDueForRenewal(Builder $query): Builder
    {
        return $query
            ->where('status', SubscriptionStatus::Active->value)
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', Date::now());
    }
}
