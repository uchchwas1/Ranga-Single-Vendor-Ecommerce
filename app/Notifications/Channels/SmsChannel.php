<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Notifications\Senders\SmsSender;
use Illuminate\Notifications\Notification;

/**
 * Notification channel that delivers via the configured SMS sender.
 */
class SmsChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(
        private readonly SmsSender $sender,
    ) {
    }

    /**
     * Send the given notification.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        /** @var array{to?: string|null, message: string} $payload */
        $payload = $notification->toSms($notifiable);
        $to = $payload['to'] ?? $notifiable->routeNotificationFor('sms', $notification);

        if (! is_string($to) || $to === '') {
            return;
        }

        $this->sender->send($to, $payload['message']);
    }
}
