<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Lifecycle status of an affiliate account.
 */
enum AffiliateStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return __('marketing.affiliate_status.'.$this->value);
    }
}
