<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Status of a refund.
 */
enum RefundStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return __('commerce.refund_status.'.$this->value);
    }
}
