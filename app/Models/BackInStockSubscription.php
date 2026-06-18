<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A request to be notified when a variant is back in stock.
 *
 * @property string $id
 * @property string $variant_id
 * @property string|null $user_id
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $notified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class BackInStockSubscription extends Model
{
    /** @use HasFactory<\Database\Factories\BackInStockSubscriptionFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'variant_id',
        'user_id',
        'email',
        'notified_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'notified_at' => 'datetime',
        ];
    }

    /**
     * The watched variant.
     *
     * @return BelongsTo<ProductVariant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    /**
     * Scope to subscriptions still awaiting notification.
     *
     * @param  Builder<BackInStockSubscription>  $query
     * @return Builder<BackInStockSubscription>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('notified_at');
    }
}
