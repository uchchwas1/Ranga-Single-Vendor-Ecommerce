<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Ranga — White-Label Platform Configuration
|--------------------------------------------------------------------------
| Every deployment-specific value must come from the environment so the
| platform can be resold with zero core code changes.
*/

return [

    'brand' => [
        'name' => env('RANGA_BRAND_NAME', 'Ranga'),
        'tagline' => env('RANGA_BRAND_TAGLINE', 'Dresses that speak your colour'),
        'logo' => env('RANGA_BRAND_LOGO', '/images/logo.svg'),
        'color' => env('RANGA_BRAND_COLOR', '#e11d48'),
    ],

    'defaults' => [
        'locale' => env('RANGA_DEFAULT_LOCALE', 'bn'),
        'fallback_locale' => env('RANGA_FALLBACK_LOCALE', 'en'),
        'timezone' => env('RANGA_DEFAULT_TIMEZONE', 'Asia/Dhaka'),
        'currency' => env('RANGA_CURRENCY', 'BDT'),
        'currency_symbol' => env('RANGA_CURRENCY_SYMBOL', '৳'),
        'date_format' => env('RANGA_DATE_FORMAT', 'd/m/Y'),
    ],

    'validation' => [
        // Bangladeshi mobile numbers: +8801XXXXXXXXX, 8801XXXXXXXXX or 01XXXXXXXXX
        'phone_regex' => env('RANGA_PHONE_REGEX', '/^(?:\+?880|0)1[3-9]\d{8}$/'),
    ],

    'auth' => [
        'two_factor_challenge_ttl' => (int) env('RANGA_2FA_CHALLENGE_TTL', 5),
        'token_expiration_minutes' => (int) env('SANCTUM_TOKEN_EXPIRATION', 43200),
    ],

];
