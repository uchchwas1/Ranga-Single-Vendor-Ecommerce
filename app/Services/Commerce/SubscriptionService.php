<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Events\Commerce\SubscriptionRenewed;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Subscription;
use App\Models\User;
use App\Support\Enums\SubscriptionInterval;
use App\Support\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\Date;

/**
 * Application service for the internal subscription billing engine.
 * Stripe Billing can be layered behind this contract later.
 */
class SubscriptionService
{
    /**
     * Start a new subscription for a user.
     */
    public function create(
        User $user,
        Product $product,
        ?ProductVariant $variant,
        SubscriptionInterval $interval,
        int $quantity = 1,
    ): Subscription {
        $price = $variant !== null ? (float) $variant->price : ($product->priceFrom() ?? 0.0);
        $now = Date::now();

        /** @var Subscription $subscription */
        $subscription = $user->subscriptions()->create([
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'interval' => $interval,
            'status' => SubscriptionStatus::Active,
            'price' => $price,
            'quantity' => max(1, $quantity),
            'started_at' => $now,
            'next_billing_at' => $interval->next($now),
        ]);

        return $subscription;
    }

    /**
     * Pause an active subscription.
     */
    public function pause(Subscription $subscription): Subscription
    {
        $subscription->forceFill(['status' => SubscriptionStatus::Paused])->save();

        return $subscription;
    }

    /**
     * Resume a paused subscription, rescheduling overdue billing.
     */
    public function resume(Subscription $subscription): Subscription
    {
        $next = $subscription->next_billing_at;
        $now = Date::now();

        $subscription->forceFill([
            'status' => SubscriptionStatus::Active,
            'next_billing_at' => ($next === null || $next->isPast()) ? $subscription->interval->next($now) : $next,
        ])->save();

        return $subscription;
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Subscription $subscription): Subscription
    {
        $subscription->forceFill([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => Date::now(),
            'next_billing_at' => null,
        ])->save();

        return $subscription;
    }

    /**
     * Renew a subscription: advance the billing date and emit an event so
     * the configured payment backend can charge for the cycle.
     */
    public function renew(Subscription $subscription): Subscription
    {
        $from = $subscription->next_billing_at ?? Date::now();

        $subscription->forceFill([
            'next_billing_at' => $subscription->interval->next($from),
        ])->save();

        SubscriptionRenewed::dispatch($subscription);

        return $subscription;
    }
}
