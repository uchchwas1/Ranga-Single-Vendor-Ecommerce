<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * The kind of discount a coupon applies.
 */
enum CouponType: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';
    case FreeShipping = 'free_shipping';

    /**
     * Human-readable label for the type.
     */
    public function label(): string
    {
        return __('marketing.coupon_type.'.$this->value);
    }
}
