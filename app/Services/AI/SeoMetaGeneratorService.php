<?php

declare(strict_types=1);

namespace App\Services\AI;

use Illuminate\Support\Str;

/**
 * Generates SEO meta title/description/keywords via the AI provider.
 */
class SeoMetaGeneratorService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly AiManager $ai,
    ) {
    }

    /**
     * Generate SEO metadata for the given context.
     *
     * @return array{title: string, description: string, keywords: list<string>}
     */
    public function generate(string $context): array
    {
        $prompt = 'Generate SEO metadata for the following item and respond with strict JSON '
            .'{"title": string (<=60 chars), "description": string (<=160 chars), "keywords": string[]}. '
            ."Item: {$context}";

        $raw = $this->ai->driver()->complete($prompt);

        /** @var array<string, mixed>|null $decoded */
        $decoded = json_decode($this->stripFences($raw), true);

        if (! is_array($decoded)) {
            return [
                'title' => Str::limit($context, 57),
                'description' => Str::limit($context, 157),
                'keywords' => [],
            ];
        }

        $keywords = $decoded['keywords'] ?? [];

        return [
            'title' => Str::limit((string) ($decoded['title'] ?? $context), 60, ''),
            'description' => Str::limit((string) ($decoded['description'] ?? $context), 160, ''),
            'keywords' => is_array($keywords) ? array_values(array_map('strval', $keywords)) : [],
        ];
    }

    /**
     * Strip Markdown code fences a model may wrap JSON in.
     */
    private function stripFences(string $text): string
    {
        $text = trim($text);
        $text = (string) preg_replace('/^```(?:json)?/i', '', $text);

        return trim((string) preg_replace('/```$/', '', $text));
    }
}
