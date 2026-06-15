<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * How an affiliate commission is calculated.
 */
enum CommissionType: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';

    /**
     * Compute the commission for a given order total.
     */
    public function commissionFor(float $orderTotal, float $rate): float
    {
        return match ($this) {
            self::Percent => round($orderTotal * ($rate / 100), 2),
            self::Fixed => round($rate, 2),
        };
    }
}
