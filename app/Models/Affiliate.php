<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\AffiliateStatus;
use App\Support\Enums\CommissionType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * An affiliate partner who earns commission on referred orders.
 *
 * @property string $id
 * @property string $user_id
 * @property string $code
 * @property string $commission_rate
 * @property CommissionType $commission_type
 * @property string $earnings_total
 * @property string $paid_total
 * @property AffiliateStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Affiliate extends Model
{
    /** @use HasFactory<\Database\Factories\AffiliateFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'code',
        'commission_rate',
        'commission_type',
        'earnings_total',
        'paid_total',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'commission_type' => CommissionType::class,
            'earnings_total' => 'decimal:2',
            'paid_total' => 'decimal:2',
            'status' => AffiliateStatus::class,
        ];
    }

    /**
     * The user behind this affiliate account.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Recorded referral clicks.
     *
     * @return HasMany<AffiliateClick, $this>
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class);
    }

    /**
     * Recorded conversions (commissions).
     *
     * @return HasMany<AffiliateConversion, $this>
     */
    public function conversions(): HasMany
    {
        return $this->hasMany(AffiliateConversion::class);
    }

    /**
     * Use the code for route-model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
