<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\CouponRepositoryContract;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\ValidationException;

/**
 * Application service for coupon validation and redemption.
 */
class CouponService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly CouponRepositoryContract $coupons,
    ) {
    }

    /**
     * Validate a coupon code against a cart subtotal and user.
     *
     * @throws ValidationException
     */
    public function validate(string $code, float $subtotal, ?User $user = null): Coupon
    {
        $coupon = $this->coupons->findByCode($code);

        if ($coupon === null || ! $coupon->isRedeemable()) {
            throw ValidationException::withMessages(['coupon' => [__('marketing.coupon.invalid')]]);
        }

        if ($subtotal < (float) $coupon->min_order_amount) {
            throw ValidationException::withMessages([
                'coupon' => [__('marketing.coupon.min_order', ['amount' => $coupon->min_order_amount])],
            ]);
        }

        if ($coupon->user_limit !== null && $user !== null) {
            $used = $coupon->usages()->where('user_id', $user->id)->count();

            if ($used >= $coupon->user_limit) {
                throw ValidationException::withMessages(['coupon' => [__('marketing.coupon.user_limit')]]);
            }
        }

        return $coupon;
    }

    /**
     * Record a redemption: bump the usage counter and log the usage row.
     */
    public function recordUsage(Coupon $coupon, ?User $user, Order $order, float $discount): void
    {
        $coupon->increment('used_count');

        $coupon->usages()->create([
            'user_id' => $user?->id,
            'order_id' => $order->id,
            'discount_amount' => $discount,
            'used_at' => Date::now(),
        ]);
    }
}
