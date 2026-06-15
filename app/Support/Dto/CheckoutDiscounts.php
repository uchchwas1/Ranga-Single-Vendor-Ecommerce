<?php

declare(strict_types=1);

namespace App\Support\Dto;

use App\Models\Coupon;
use App\Models\GiftCard;

/**
 * Computed discount breakdown for a checkout, before side effects are
 * applied (coupon usage, gift-card deduction, points redemption).
 */
final readonly class CheckoutDiscounts
{
    public function __construct(
        public ?Coupon $coupon,
        public float $couponDiscount,
        public bool $freeShipping,
        public ?GiftCard $giftCard,
        public float $giftCardApplied,
        public int $loyaltyPoints,
        public float $loyaltyApplied,
        public float $discountAmount,
        public float $shippingPayable,
        public float $total,
    ) {
    }
}
