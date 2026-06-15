<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\User;
use App\Support\Dto\CheckoutDiscounts;
use App\Support\Enums\CouponType;
use Illuminate\Validation\ValidationException;

/**
 * Orchestrates the order of operations for checkout discounts:
 * coupon -> loyalty redemption -> gift card, returning a pure breakdown.
 */
class DiscountService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly CouponService $coupons,
        private readonly GiftCardService $giftCards,
        private readonly LoyaltyService $loyalty,
    ) {
    }

    /**
     * Compute the discount breakdown for a checkout. Validates the coupon,
     * gift card and points but performs no mutations.
     *
     * @throws ValidationException
     */
    public function forCheckout(
        float $subtotal,
        float $shipping,
        float $tax,
        ?User $user = null,
        ?string $couponCode = null,
        ?string $giftCardCode = null,
        int $redeemPoints = 0,
    ): CheckoutDiscounts {
        $coupon = null;
        $couponDiscount = 0.0;
        $freeShipping = false;

        if ($couponCode !== null && $couponCode !== '') {
            $coupon = $this->coupons->validate($couponCode, $subtotal, $user);

            if ($coupon->type === CouponType::FreeShipping) {
                $freeShipping = true;
            } else {
                $couponDiscount = $coupon->discountFor($subtotal);
            }
        }

        $shippingPayable = $freeShipping ? 0.0 : $shipping;

        $loyaltyApplied = 0.0;
        if ($redeemPoints > 0) {
            if ($user === null) {
                throw ValidationException::withMessages(['points' => [__('marketing.loyalty.login_required')]]);
            }

            if ($redeemPoints > $user->loyalty_points) {
                throw ValidationException::withMessages(['points' => [__('marketing.loyalty.insufficient_points')]]);
            }

            $runningTotal = max(0.0, $subtotal - $couponDiscount + $shippingPayable + $tax);
            $loyaltyApplied = min($this->loyalty->pointsValue($redeemPoints), $runningTotal);
        }

        $preGiftCard = max(0.0, $subtotal - $couponDiscount + $shippingPayable + $tax - $loyaltyApplied);

        $giftCard = null;
        $giftCardApplied = 0.0;

        if ($giftCardCode !== null && $giftCardCode !== '') {
            $giftCard = $this->giftCards->validate($giftCardCode);
            $giftCardApplied = $this->giftCards->applicable($giftCard, $preGiftCard);
        }

        $total = max(0.0, $preGiftCard - $giftCardApplied);

        return new CheckoutDiscounts(
            coupon: $coupon,
            couponDiscount: round($couponDiscount, 2),
            freeShipping: $freeShipping,
            giftCard: $giftCard,
            giftCardApplied: round($giftCardApplied, 2),
            loyaltyPoints: $redeemPoints,
            loyaltyApplied: round($loyaltyApplied, 2),
            discountAmount: round($couponDiscount + $loyaltyApplied, 2),
            shippingPayable: round($shippingPayable, 2),
            total: round($total, 2),
        );
    }
}
