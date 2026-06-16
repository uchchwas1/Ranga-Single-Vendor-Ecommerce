<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AiProvider;
use RuntimeException;
use Illuminate\Support\Facades\Http;

/**
 * OpenAI Chat Completions provider (GPT-4o by default).
 */
class OpenAiProvider implements AiProvider
{
    /**
     * The provider's identifier.
     */
    public function name(): string
    {
        return 'openai';
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
        $config = (array) config('ai.providers.openai');

        $response = Http::withToken((string) ($config['key'] ?? ''))
            ->acceptJson()
            ->post(rtrim((string) ($config['base_url'] ?? ''), '/').'/chat/completions', [
                'model' => $options['model'] ?? $config['model'] ?? 'gpt-4o',
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? (float) config('ai.temperature', 0.7),
                'max_tokens' => $options['max_tokens'] ?? (int) config('ai.max_tokens', 800),
            ]);

        if ($response->failed()) {
            throw new RuntimeException('OpenAI request failed: '.$response->status());
        }

        $content = $response->json('choices.0.message.content');

        return is_string($content) ? trim($content) : '';
    }
}
