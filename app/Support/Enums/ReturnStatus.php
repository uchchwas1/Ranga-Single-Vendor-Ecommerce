<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Status of a customer return request.
 */
enum ReturnStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return __('commerce.return_status.'.$this->value);
    }

    /**
     * Whether the request is still awaiting an admin decision.
     */
    public function isPending(): bool
    {
        return $this === self::Pending;
    }
}
