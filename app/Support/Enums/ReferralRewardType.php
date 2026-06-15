<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * The kind of reward granted for a successful referral.
 */
enum ReferralRewardType: string
{
    case Points = 'points';
    case Fixed = 'fixed';
}
