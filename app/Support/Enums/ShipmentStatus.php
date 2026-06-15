<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Status of an individual shipment.
 */
enum ShipmentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Returned = 'returned';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return __('commerce.shipment_status.'.$this->value);
    }
}
