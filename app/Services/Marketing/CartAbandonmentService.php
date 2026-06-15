<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\Cart;
use App\Models\CartAbandonment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;

/**
 * Detects abandoned carts and records them for recovery outreach.
 */
class CartAbandonmentService
{
    /**
     * Flag carts that have been idle beyond the configured threshold and
     * still contain items, returning the newly recorded abandonments.
     *
     * @return Collection<int, CartAbandonment>
     */
    public function flagAbandoned(): Collection
    {
        $threshold = Date::now()->subMinutes((int) config('ranga.cart.abandon_after_minutes', 60));

        $carts = Cart::query()
            ->where('updated_at', '<=', $threshold)
            ->whereHas('items')
            ->whereDoesntHave('abandonment')
            ->with(['items', 'user'])
            ->limit(200)
            ->get();

        return $carts->map(function (Cart $cart): CartAbandonment {
            /** @var CartAbandonment $abandonment */
            $abandonment = CartAbandonment::query()->create([
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'email' => $cart->user?->email,
                'total' => $cart->subtotal(),
                'recovered' => false,
            ]);

            return $abandonment;
        });
    }

    /**
     * Mark an abandonment's recovery email as sent.
     */
    public function markEmailSent(CartAbandonment $abandonment): void
    {
        $abandonment->forceFill(['recovery_email_sent_at' => Date::now()])->save();
    }
}
