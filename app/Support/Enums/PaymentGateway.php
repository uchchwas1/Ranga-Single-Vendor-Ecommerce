<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Supported payment gateway codes.
 *
 * COD, SSLCommerz, bKash and Stripe are implemented in Phase 3; the
 * remaining codes are reserved for later phases.
 */
enum PaymentGateway: string
{
    case Sslcommerz = 'sslcommerz';
    case Bkash = 'bkash';
    case Nagad = 'nagad';
    case Stripe = 'stripe';
    case Paypal = 'paypal';
    case Cod = 'cod';
    case GiftCard = 'gift_card';
    case Loyalty = 'loyalty';

    /**
     * Human-readable label for the gateway.
     */
    public function label(): string
    {
        return __('commerce.gateway.'.$this->value);
    }

    /**
     * Whether the gateway is implemented and available in this phase.
     */
    public function isImplemented(): bool
    {
        return match ($this) {
            self::Cod, self::Sslcommerz, self::Bkash, self::Stripe => true,
            default => false,
        };
    }

    /**
     * Whether the gateway redirects/handshakes off-site before payment.
     */
    public function isOffsite(): bool
    {
        return match ($this) {
            self::Sslcommerz, self::Bkash, self::Stripe, self::Paypal, self::Nagad => true,
            self::Cod, self::GiftCard, self::Loyalty => false,
        };
    }
}
