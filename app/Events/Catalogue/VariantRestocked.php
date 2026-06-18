<?php

declare(strict_types=1);

namespace App\Events\Catalogue;

use App\Models\ProductVariant;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Raised when a variant transitions from out-of-stock to in-stock.
 */
class VariantRestocked
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ProductVariant $variant,
    ) {
    }
}
