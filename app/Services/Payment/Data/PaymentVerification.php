<?php

declare(strict_types=1);

namespace App\Services\Payment\Data;

use App\Support\Enums\PaymentStatus;

/**
 * The result of verifying a payment with a gateway.
 */
final readonly class PaymentVerification
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public bool $successful,
        public PaymentStatus $status,
        public ?string $transactionId = null,
        public array $raw = [],
    ) {
    }
}
