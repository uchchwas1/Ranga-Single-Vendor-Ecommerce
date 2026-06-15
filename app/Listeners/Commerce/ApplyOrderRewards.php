<?php

declare(strict_types=1);

namespace App\Listeners\Commerce;

use App\Events\Commerce\OrderPlaced;
use App\Services\Marketing\LoyaltyService;
use App\Services\Marketing\ReferralService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Awards loyalty points and grants any referral reward when an order
 * is placed. Covers both COD and online orders uniformly.
 */
class ApplyOrderRewards implements ShouldQueue
{
    /**
     * Create a new listener instance.
     */
    public function __construct(
        private readonly LoyaltyService $loyalty,
        private readonly ReferralService $referrals,
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;
        $user = $order->user;

        if ($user !== null) {
            $points = $this->loyalty->earnableFor((float) $order->total);

            if ($points > 0) {
                $this->loyalty->earn($user, $points, $order, 'Order '.$order->order_number);
            }
        }

        $this->referrals->rewardForOrder($order);
    }
}
