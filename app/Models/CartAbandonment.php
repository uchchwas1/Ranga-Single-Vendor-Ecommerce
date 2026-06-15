<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A record of an abandoned cart eligible for recovery outreach.
 *
 * @property string $id
 * @property string $cart_id
 * @property string|null $user_id
 * @property string|null $email
 * @property string $total
 * @property bool $recovered
 * @property \Illuminate\Support\Carbon|null $recovery_email_sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CartAbandonment extends Model
{
    /** @use HasFactory<\Database\Factories\CartAbandonmentFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cart_id',
        'user_id',
        'email',
        'total',
        'recovered',
        'recovery_email_sent_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'recovered' => 'boolean',
            'recovery_email_sent_at' => 'datetime',
        ];
    }

    /**
     * The abandoned cart.
     *
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }
}
