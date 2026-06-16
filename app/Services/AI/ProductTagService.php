<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Generates product tags (English + Bangla) via the AI provider.
 */
class ProductTagService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly AiManager $ai,
    ) {
    }

    /**
     * Generate a list of relevant tags for a product.
     *
     * @return list<string>
     */
    public function generate(string $description, ?string $category = null): array
    {
        $prompt = 'Return 5-10 short product tags (comma-separated, no numbering) relevant to this '
            .'fashion product'.($category !== null ? " in the {$category} category" : '').'. '
            ."Include both English and Bangla tags. Description: {$description}";

        $raw = $this->ai->driver()->complete($prompt);

        $tags = preg_split('/[,\n]+/', $raw) ?: [];

        return array_values(array_filter(array_map(
            static fn (string $tag): string => trim($tag, " \t\n\r\0\x0B-•*"),
            $tags,
        ), static fn (string $tag): bool => $tag !== ''));
    }
}
