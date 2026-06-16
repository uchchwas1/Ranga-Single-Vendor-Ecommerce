<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_chat_with_the_assistant(): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => 'We offer 7-day returns.']]],
            ]),
        ]);

        $this->postJson('/api/v1/chat', ['message' => 'What is your return policy?'])
            ->assertOk()
            ->assertJsonPath('reply', 'We offer 7-day returns.');
    }

    public function test_chat_requires_a_message(): void
    {
        $this->postJson('/api/v1/chat', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['message']);
    }
}
