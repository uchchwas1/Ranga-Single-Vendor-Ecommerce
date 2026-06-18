<?php

declare(strict_types=1);

namespace App\Events\Commerce;

use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Raised when a subscription is renewed for its next billing cycle.
 */
class SubscriptionRenewed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Subscription $subscription,
    ) {
    }
}
