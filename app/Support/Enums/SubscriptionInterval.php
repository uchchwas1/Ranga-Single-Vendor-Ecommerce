<?php

declare(strict_types=1);

namespace App\Support\Enums;

use Illuminate\Support\Carbon;

/**
 * Billing cadence for a subscription product.
 */
enum SubscriptionInterval: string
{
    case Weekly = 'weekly';
    case Monthly = 'monthly';

    /**
     * The next billing date from a given point.
     */
    public function next(Carbon $from): Carbon
    {
        return match ($this) {
            self::Weekly => $from->copy()->addWeek(),
            self::Monthly => $from->copy()->addMonthNoOverflow(),
        };
    }

    /**
     * Human-readable label for the interval.
     */
    public function label(): string
    {
        return __('bonus.interval.'.$this->value);
    }
}
