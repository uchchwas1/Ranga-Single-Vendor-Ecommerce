<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\ReferralRewardStatus;
use App\Support\Enums\ReferralRewardType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A reward granted to a referrer when their referee converts.
 *
 * @property string $id
 * @property string $referrer_id
 * @property string $referred_id
 * @property string|null $order_id
 * @property ReferralRewardType $reward_type
 * @property string $reward_value
 * @property ReferralRewardStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ReferralReward extends Model
{
    /** @use HasFactory<\Database\Factories\ReferralRewardFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'order_id',
        'reward_type',
        'reward_value',
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
            'reward_type' => ReferralRewardType::class,
            'reward_value' => 'decimal:2',
            'status' => ReferralRewardStatus::class,
        ];
    }

    /**
     * The user who referred.
     *
     * @return BelongsTo<User, $this>
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * The referred user.
     *
     * @return BelongsTo<User, $this>
     */
    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
