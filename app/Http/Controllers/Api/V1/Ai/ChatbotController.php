<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Ai;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\ChatRequest;
use App\Services\AI\ChatbotService;
use Illuminate\Http\JsonResponse;

/**
 * Customer-support chatbot endpoint.
 */
class ChatbotController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ChatbotService $chatbot,
    ) {
    }

    /**
     * POST /chat — get an assistant reply for a customer message.
     */
    public function reply(ChatRequest $request): JsonResponse
    {
        /** @var list<array{role: string, content: string}> $history */
        $history = $request->validated('history') ?? [];

        $reply = $this->chatbot->reply((string) $request->validated('message'), $history);

        return new JsonResponse(['reply' => $reply]);
    }
}
