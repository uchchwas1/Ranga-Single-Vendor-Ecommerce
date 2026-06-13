<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A shopping cart, owned by a user or an anonymous session.
 *
 * @property string $id
 * @property string|null $user_id
 * @property string|null $session_id
 * @property string $currency
 * @property string|null $coupon_id
 * @property string|null $gift_card_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Cart extends Model
{
    /** @use HasFactory<\Database\Factories\CartFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'session_id',
        'currency',
        'coupon_id',
        'gift_card_id',
    ];

    /**
     * The owning user, if the cart is not anonymous.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Line items in the cart.
     *
     * @return HasMany<CartItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * The cart's running subtotal across all line items.
     */
    public function subtotal(): float
    {
        return (float) $this->items->sum(
            static fn (CartItem $item): float => (float) $item->price_at_add * $item->quantity,
        );
    }
}
