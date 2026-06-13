<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * The role an order address plays.
 */
enum AddressType: string
{
    case Shipping = 'shipping';
    case Billing = 'billing';
}
