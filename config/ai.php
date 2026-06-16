<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    | Supported: "openai", "gemini".
    */

    'default' => env('AI_PROVIDER', 'openai'),

    'providers' => [

        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        ],

        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Generation Defaults
    |--------------------------------------------------------------------------
    */

    'temperature' => (float) env('AI_TEMPERATURE', 0.7),
    'max_tokens' => (int) env('AI_MAX_TOKENS', 800),

    /*
    |--------------------------------------------------------------------------
    | Chatbot
    |--------------------------------------------------------------------------
    */

    'chatbot' => [
        'system_prompt' => env(
            'AI_CHATBOT_SYSTEM_PROMPT',
            'You are a helpful customer-support assistant for a Bangladeshi fashion store. '
            .'Answer concisely about products, orders, shipping, returns and payments. '
            .'If you cannot help, suggest opening a support ticket.',
        ),
    ],

];
