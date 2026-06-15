<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\Order;
use App\Models\ReferralReward;
use App\Support\Enums\ReferralRewardStatus;
use App\Support\Enums\ReferralRewardType;

/**
 * Application service for the referral reward programme.
 */
class ReferralService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly LoyaltyService $loyalty,
    ) {
    }

    /**
     * Grant the referrer a reward when their referee's order is paid.
     *
     * One reward per referrer/referee pair.
     */
    public function rewardForOrder(Order $order): ?ReferralReward
    {
        $referee = $order->user;

        if ($referee === null || $referee->referred_by === null) {
            return null;
        }

        $exists = ReferralReward::query()
            ->where('referrer_id', $referee->referred_by)
            ->where('referred_id', $referee->id)
            ->exists();

        if ($exists) {
            return null;
        }

        $type = ReferralRewardType::tryFrom((string) config('ranga.referral.reward_type', 'points')) ?? ReferralRewardType::Points;
        $value = (float) config('ranga.referral.reward_value', 100);

        /** @var ReferralReward $reward */
        $reward = ReferralReward::query()->create([
            'referrer_id' => $referee->referred_by,
            'referred_id' => $referee->id,
            'order_id' => $order->id,
            'reward_type' => $type,
            'reward_value' => $value,
            'status' => ReferralRewardStatus::Granted,
        ]);

        if ($type === ReferralRewardType::Points && $referee->referrer !== null) {
            $this->loyalty->earn($referee->referrer, (int) $value, $order, 'Referral reward');
        }

        return $reward;
    }
}
