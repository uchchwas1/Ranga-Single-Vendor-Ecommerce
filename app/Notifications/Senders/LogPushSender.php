<?php

declare(strict_types=1);

namespace App\Notifications\Senders;

use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

/**
 * Default push sender: logs the payload. Swap for a Web Push / FCM
 * implementation in production.
 */
class LogPushSender implements PushSender
{
    /**
     * Deliver a push payload to a subscription endpoint.
     *
     * @param  array<string, mixed>  $payload
     */
    public function send(PushSubscription $subscription, array $payload): void
    {
        Log::info('Push dispatched', [
            'endpoint' => $subscription->endpoint,
            'title' => $payload['title'] ?? null,
        ]);
    }
}
