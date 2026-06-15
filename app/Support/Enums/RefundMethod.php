<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * How a refund is returned to the customer.
 */
enum RefundMethod: string
{
    case OriginalPayment = 'original_payment';
    case StoreCredit = 'store_credit';
    case Manual = 'manual';

    /**
     * Human-readable label for the method.
     */
    public function label(): string
    {
        return __('commerce.refund_method.'.$this->value);
    }

    /**
     * Whether this method triggers a gateway refund call.
     */
    public function usesGateway(): bool
    {
        return $this === self::OriginalPayment;
    }
}
