<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Status of a referral reward.
 */
enum ReferralRewardStatus: string
{
    case Pending = 'pending';
    case Granted = 'granted';
    case Expired = 'expired';
}
