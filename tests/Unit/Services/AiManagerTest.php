<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\AI\AiManager;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OpenAiProvider;
use InvalidArgumentException;
use Tests\TestCase;

class AiManagerTest extends TestCase
{
    private function manager(): AiManager
    {
        return app(AiManager::class);
    }

    public function test_it_resolves_the_openai_provider(): void
    {
        $this->assertInstanceOf(OpenAiProvider::class, $this->manager()->driver('openai'));
    }

    public function test_it_resolves_the_gemini_provider(): void
    {
        $this->assertInstanceOf(GeminiProvider::class, $this->manager()->driver('gemini'));
    }

    public function test_it_defaults_to_the_configured_provider(): void
    {
        config()->set('ai.default', 'gemini');

        $this->assertInstanceOf(GeminiProvider::class, $this->manager()->driver());
    }

    public function test_it_throws_for_an_unknown_provider(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->manager()->driver('bogus');
    }
}
