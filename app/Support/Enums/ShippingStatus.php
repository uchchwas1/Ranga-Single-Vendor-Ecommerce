<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Fulfilment status of an order shipment.
 */
enum ShippingStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return __('commerce.shipping_status.'.$this->value);
    }
}
