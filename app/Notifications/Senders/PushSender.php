<?php

declare(strict_types=1);

namespace App\Notifications\Senders;

use App\Models\PushSubscription;

/**
 * Provider-agnostic web-push transport.
 */
interface PushSender
{
    /**
     * Deliver a push payload to a subscription endpoint.
     *
     * @param  array<string, mixed>  $payload
     */
    public function send(PushSubscription $subscription, array $payload): void;
}
