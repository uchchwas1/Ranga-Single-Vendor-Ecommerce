<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Models\PushSubscription;
use App\Notifications\Senders\PushSender;
use Illuminate\Notifications\Notification;

/**
 * Notification channel that delivers web-push to a user's subscriptions.
 */
class PushChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(
        private readonly PushSender $sender,
    ) {
    }

    /**
     * Send the given notification.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toPush') || ! method_exists($notifiable, 'pushSubscriptions')) {
            return;
        }

        /** @var array<string, mixed> $payload */
        $payload = $notification->toPush($notifiable);

        $notifiable->pushSubscriptions()->get()->each(function (PushSubscription $subscription) use ($payload): void {
            $this->sender->send($subscription, $payload);
        });
    }
}
