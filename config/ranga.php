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

    'tax' => [
        // Fractional VAT rate applied to the order subtotal (e.g. 0.0 = none).
        'rate' => (float) env('RANGA_TAX_RATE', 0),
    ],

    'invoices' => [
        // Filesystem disk used to store generated invoice PDFs.
        'disk' => env('RANGA_INVOICE_DISK', 'local'),
    ],

    'loyalty' => [
        // Currency spent per 1 point earned (e.g. 100 = 1 point per ৳100).
        'earn_divisor' => (float) env('RANGA_LOYALTY_EARN_DIVISOR', 100),
        // Monetary value of 1 point when redeemed (e.g. 1.0 = ৳1).
        'redeem_value' => (float) env('RANGA_LOYALTY_REDEEM_VALUE', 1),
    ],

    'referral' => [
        'reward_type' => env('RANGA_REFERRAL_REWARD_TYPE', 'points'),
        'reward_value' => (float) env('RANGA_REFERRAL_REWARD_VALUE', 100),
    ],

    'affiliate' => [
        'default_commission_rate' => (float) env('RANGA_AFFILIATE_RATE', 10),
        'default_commission_type' => env('RANGA_AFFILIATE_TYPE', 'percent'),
    ],

    'cart' => [
        // Idle minutes before a cart is considered abandoned.
        'abandon_after_minutes' => (int) env('RANGA_CART_ABANDON_MINUTES', 60),
    ],

];
