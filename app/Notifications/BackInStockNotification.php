<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifies a customer that a watched variant is back in stock.
 */
class BackInStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ProductVariant $variant,
    ) {
    }

    /**
     * The channels the notification is delivered on.
     *
     * @return list<string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    /**
     * The mail representation.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        $name = $this->variant->product?->name ?? __('bonus.back_in_stock.item');

        return (new MailMessage())
            ->subject(__('bonus.back_in_stock.subject', ['item' => $name]))
            ->line(__('bonus.back_in_stock.body', ['item' => $name]))
            ->action(__('bonus.back_in_stock.cta'), url('/products/'.($this->variant->product?->slug ?? '')));
    }
}
