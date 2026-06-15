<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\ConversionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A commission earned by an affiliate on a converted order.
 *
 * @property string $id
 * @property string $affiliate_id
 * @property string $order_id
 * @property string $commission
 * @property ConversionStatus $status
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AffiliateConversion extends Model
{
    /** @use HasFactory<\Database\Factories\AffiliateConversionFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'affiliate_id',
        'order_id',
        'commission',
        'status',
        'paid_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'commission' => 'decimal:2',
            'status' => ConversionStatus::class,
            'paid_at' => 'datetime',
        ];
    }

    /**
     * The earning affiliate.
     *
     * @return BelongsTo<Affiliate, $this>
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * The converted order.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
