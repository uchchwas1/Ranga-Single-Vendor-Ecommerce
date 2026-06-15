<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * The reason a loyalty-points balance changed.
 */
enum LoyaltyTransactionType: string
{
    case Earn = 'earn';
    case Redeem = 'redeem';
    case Expire = 'expire';
    case Adjust = 'adjust';

    /**
     * Human-readable label for the type.
     */
    public function label(): string
    {
        return __('marketing.loyalty_type.'.$this->value);
    }
}
