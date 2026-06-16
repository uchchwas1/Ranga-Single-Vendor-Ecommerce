<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use App\Notifications\Senders\WhatsAppSender;
use Illuminate\Notifications\Notification;

/**
 * Notification channel that delivers via the configured WhatsApp sender.
 */
class WhatsAppChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct(
        private readonly WhatsAppSender $sender,
    ) {
    }

    /**
     * Send the given notification.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return;
        }

        /** @var array{to?: string|null, template: string, variables?: array<string, mixed>} $payload */
        $payload = $notification->toWhatsApp($notifiable);
        $to = $payload['to'] ?? $notifiable->routeNotificationFor('whatsapp', $notification);

        if (! is_string($to) || $to === '') {
            return;
        }

        $this->sender->send($to, $payload['template'], $payload['variables'] ?? []);
    }
}
