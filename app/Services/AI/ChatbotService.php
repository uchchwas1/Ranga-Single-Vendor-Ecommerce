<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Context-aware customer-support chatbot built on the AI provider.
 */
class ChatbotService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly AiManager $ai,
    ) {
    }

    /**
     * Generate a reply to a customer message given prior turns.
     *
     * @param  list<array{role: string, content: string}>  $history
     */
    public function reply(string $message, array $history = []): string
    {
        $messages = [
            ['role' => 'system', 'content' => (string) config('ai.chatbot.system_prompt')],
            ...$history,
            ['role' => 'user', 'content' => $message],
        ];

        return $this->ai->driver()->chat($messages);
    }
}
