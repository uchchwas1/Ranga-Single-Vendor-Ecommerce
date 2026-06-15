<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Status of an affiliate conversion (commission).
 */
enum ConversionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Paid = 'paid';
    case Rejected = 'rejected';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return __('marketing.conversion_status.'.$this->value);
    }
}
