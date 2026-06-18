<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Lifecycle status of a subscription.
 */
enum SubscriptionStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Cancelled = 'cancelled';

    /**
     * Whether the subscription should be billed on its schedule.
     */
    public function isBillable(): bool
    {
        return $this === self::Active;
    }
}
