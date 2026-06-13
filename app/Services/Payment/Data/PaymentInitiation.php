<?php

declare(strict_types=1);

namespace App\Services\Payment\Data;

use App\Support\Enums\PaymentStatus;

/**
 * The result of initiating a payment with a gateway.
 */
final readonly class PaymentInitiation
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public PaymentStatus $status,
        public bool $requiresRedirect = false,
        public ?string $redirectUrl = null,
        public ?string $transactionId = null,
        public ?string $message = null,
        public array $raw = [],
    ) {
    }

    /**
     * A confirmation-based initiation (e.g. Cash on Delivery).
     */
    public static function confirmed(?string $message = null): self
    {
        return new self(status: PaymentStatus::Pending, message: $message);
    }

    /**
     * An off-site initiation requiring a customer redirect.
     *
     * @param  array<string, mixed>  $raw
     */
    public static function redirect(string $url, ?string $transactionId = null, array $raw = []): self
    {
        return new self(
            status: PaymentStatus::Pending,
            requiresRedirect: true,
            redirectUrl: $url,
            transactionId: $transactionId,
            raw: $raw,
        );
    }
}
