<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A click on an affiliate referral link.
 *
 * @property string $id
 * @property string $affiliate_id
 * @property string|null $ip
 * @property string|null $referrer
 * @property string|null $landing_page
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class AffiliateClick extends Model
{
    /** @use HasFactory<\Database\Factories\AffiliateClickFactory> */
    use HasFactory, HasUlids;

    /**
     * Only a created_at timestamp is stored.
     *
     * @var bool
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'affiliate_id',
        'ip',
        'referrer',
        'landing_page',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * The affiliate this click belongs to.
     *
     * @return BelongsTo<Affiliate, $this>
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }
}
