<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

/**
 * A stored-value gift card.
 *
 * @property string $id
 * @property string $code
 * @property string $initial_balance
 * @property string $current_balance
 * @property string $currency
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $is_active
 * @property string|null $issued_to_user_id
 * @property string|null $used_by_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class GiftCard extends Model
{
    /** @use HasFactory<\Database\Factories\GiftCardFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'initial_balance',
        'current_balance',
        'currency',
        'expires_at',
        'is_active',
        'issued_to_user_id',
        'used_by_user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'initial_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Whether the gift card can currently be redeemed.
     */
    public function isRedeemable(): bool
    {
        if (! $this->is_active || (float) $this->current_balance <= 0) {
            return false;
        }

        return $this->expires_at === null || Date::now()->lte($this->expires_at);
    }

    /**
     * Use the code for route-model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
