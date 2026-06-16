<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Generates marketing product descriptions via the configured AI provider.
 */
class ProductDescriptionService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly AiManager $ai,
    ) {
    }

    /**
     * Generate an HTML-formatted description for a product.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function generate(string $name, ?string $category = null, array $attributes = []): string
    {
        $attributeText = $attributes === []
            ? ''
            : ' Attributes: '.implode(', ', array_map(
                static fn (mixed $v, string $k): string => "{$k}={$v}",
                $attributes,
                array_keys($attributes),
            )).'.';

        $prompt = "Write an engaging, SEO-friendly HTML product description (2 short paragraphs) "
            ."for a fashion product named \"{$name}\""
            .($category !== null ? " in the {$category} category." : '.')
            .$attributeText
            .' Target young women in Bangladesh. Return HTML only.';

        return $this->ai->driver()->complete($prompt);
    }
}
