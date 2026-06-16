<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Generates personalised abandoned-cart re-engagement email copy.
 */
class AbandonedCartCopyService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly AiManager $ai,
    ) {
    }

    /**
     * Generate re-engagement copy for the given cart products.
     *
     * @param  list<string>  $productNames
     */
    public function generate(array $productNames): string
    {
        $items = $productNames === [] ? 'your selected items' : implode(', ', $productNames);

        $prompt = "Write a short, warm abandoned-cart recovery email (2-3 sentences) encouraging the "
            ."customer to complete their purchase of: {$items}. Add a gentle urgency trigger. Plain text.";

        return $this->ai->driver()->complete($prompt);
    }
}
