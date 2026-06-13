<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Status of a payment against an order.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return __('commerce.payment_status.'.$this->value);
    }
}
