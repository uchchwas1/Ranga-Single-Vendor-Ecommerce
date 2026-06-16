<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\PushChannel;
use App\Notifications\Channels\SmsChannel;
use App\Notifications\Channels\WhatsAppChannel;
use App\Support\Enums\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Notifies a customer that their order status changed, across
 * database, SMS, WhatsApp and push channels.
 */
class OrderStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Order $order,
        private readonly OrderStatus $status,
    ) {
    }

    /**
     * The channels the notification is delivered on.
     *
     * @return list<string|class-string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database', SmsChannel::class, WhatsAppChannel::class, PushChannel::class];
    }

    /**
     * The database representation.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'order_number' => $this->order->order_number,
            'status' => $this->status->value,
            'message' => $this->line(),
        ];
    }

    /**
     * The SMS representation.
     *
     * @return array{message: string}
     */
    public function toSms(mixed $notifiable): array
    {
        return ['message' => $this->line()];
    }

    /**
     * The WhatsApp representation.
     *
     * @return array{template: string, variables: array<string, mixed>}
     */
    public function toWhatsApp(mixed $notifiable): array
    {
        return [
            'template' => 'order_status_update',
            'variables' => [
                'order_number' => $this->order->order_number,
                'status' => $this->status->label(),
            ],
        ];
    }

    /**
     * The push representation.
     *
     * @return array<string, mixed>
     */
    public function toPush(mixed $notifiable): array
    {
        return [
            'title' => __('notifications.order_status.title'),
            'body' => $this->line(),
            'data' => ['order_number' => $this->order->order_number],
        ];
    }

    /**
     * The shared message line.
     */
    private function line(): string
    {
        return __('notifications.order_status.body', [
            'order' => $this->order->order_number,
            'status' => $this->status->label(),
        ]);
    }
}
