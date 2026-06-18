<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * When payment is collected for a pre-order product.
 */
enum PreorderPayment: string
{
    case Now = 'now';
    case Later = 'later';
}
