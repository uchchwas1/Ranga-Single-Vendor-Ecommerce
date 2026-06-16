<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Contracts\AiProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OpenAiProvider;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Resolves the configured AI provider (factory). Backends are
 * interchangeable behind the AiProvider contract.
 */
class AiManager
{
    /**
     * Map of provider names to their implementations.
     *
     * @var array<string, class-string<AiProvider>>
     */
    private const PROVIDERS = [
        'openai' => OpenAiProvider::class,
        'gemini' => GeminiProvider::class,
    ];

    /**
     * Create a new manager instance.
     */
    public function __construct(
        private readonly Container $container,
    ) {
    }

    /**
     * Resolve an AI provider by name (defaults to the configured provider).
     *
     * @throws InvalidArgumentException
     */
    public function driver(?string $name = null): AiProvider
    {
        $name ??= (string) config('ai.default', 'openai');
        $class = self::PROVIDERS[$name] ?? null;

        if ($class === null) {
            throw new InvalidArgumentException("Unsupported AI provider [{$name}].");
        }

        /** @var AiProvider $provider */
        $provider = $this->container->make($class);

        return $provider;
    }
}
