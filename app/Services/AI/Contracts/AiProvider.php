<?php

declare(strict_types=1);

namespace App\Services\AI\Contracts;

/**
 * Provider-agnostic contract for large-language-model backends.
 */
interface AiProvider
{
    /**
     * Complete a single-turn prompt and return the generated text.
     *
     * @param  array<string, mixed>  $options
     */
    public function complete(string $prompt, array $options = []): string;

    /**
     * Run a multi-turn chat completion and return the assistant's reply.
     *
     * @param  list<array{role: string, content: string}>  $messages
     * @param  array<string, mixed>  $options
     */
    public function chat(array $messages, array $options = []): string;

    /**
     * The provider's identifier (e.g. "openai", "gemini").
     */
    public function name(): string;
}
