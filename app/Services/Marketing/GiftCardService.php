<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\GiftCard;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * Application service for gift-card validation and redemption.
 */
class GiftCardService
{
    /**
     * Validate a gift-card code.
     *
     * @throws ValidationException
     */
    public function validate(string $code): GiftCard
    {
        $card = GiftCard::query()->where('code', $code)->first();

        if ($card === null || ! $card->isRedeemable()) {
            throw ValidationException::withMessages(['gift_card' => [__('marketing.gift_card.invalid')]]);
        }

        return $card;
    }

    /**
     * The amount of a card that can be applied to a payable total.
     */
    public function applicable(GiftCard $card, float $amountDue): float
    {
        return round(min((float) $card->current_balance, max(0, $amountDue)), 2);
    }

    /**
     * Deduct an amount from the card's balance, returning the amount applied.
     */
    public function redeem(GiftCard $card, float $amount, ?User $user = null): float
    {
        $applied = $this->applicable($card, $amount);

        $card->current_balance = (float) $card->current_balance - $applied;
        $card->used_by_user_id = $user?->id ?? $card->used_by_user_id;
        $card->save();

        return $applied;
    }
}
