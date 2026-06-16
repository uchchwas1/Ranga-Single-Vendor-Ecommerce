<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Google Gemini provider (1.5 Flash by default).
 */
class GeminiProvider implements AiProvider
{
    /**
     * The provider's identifier.
     */
    public function name(): string
    {
        return 'gemini';
    }

    /**
     * Complete a single-turn prompt.
     *
     * @param  array<string, mixed>  $options
     */
    public function complete(string $prompt, array $options = []): string
    {
        return $this->chat([['role' => 'user', 'content' => $prompt]], $options);
    }

    /**
     * Run a chat completion.
     *
     * @param  list<array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     */
    public function chat(array $messages, array $options = []): string
    {
        $config = (array) config('ai.providers.gemini');
        $model = (string) ($options['model'] ?? $config['model'] ?? 'gemini-1.5-flash');

        $contents = array_map(static fn (array $m): array => [
            'role' => $m['role'] === 'assistant' ? 'model' : 'user',
            'parts' => [['text' => $m['content']]],
        ], $messages);

        $response = Http::acceptJson()->post(
            rtrim((string) ($config['base_url'] ?? ''), '/').'/models/'.$model.':generateContent?key='.(string) ($config['key'] ?? ''),
            ['contents' => $contents],
        );

        if ($response->failed()) {
            throw new RuntimeException('Gemini request failed: '.$response->status());
        }

        $text = $response->json('candidates.0.content.parts.0.text');

        return is_string($text) ? trim($text) : '';
    }
}
