<?php

declare(strict_types=1);

namespace App\Support\Dto;

use App\Models\Order;
use App\Services\Payment\Data\PaymentInitiation;

/**
 * The outcome of placing an order: the order plus its payment initiation.
 */
final readonly class CheckoutResult
{
    public function __construct(
        public Order $order,
        public PaymentInitiation $initiation,
    ) {
    }
}
