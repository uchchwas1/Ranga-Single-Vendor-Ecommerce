<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Lifecycle status of an order.
 */
enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return __('commerce.order_status.'.$this->value);
    }

    /**
     * Whether the order may still be cancelled by the customer.
     */
    public function isCancellable(): bool
    {
        return match ($this) {
            self::Pending, self::Confirmed, self::Processing => true,
            default => false,
        };
    }
}
