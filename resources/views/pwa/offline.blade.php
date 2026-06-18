<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('ranga.brand.name') }} — {{ __('bonus.pwa.offline_title') }}</title>
    <style>
        body { font-family: sans-serif; display: grid; place-items: center; min-height: 100vh; margin: 0; background: #f9fafb; color: #1f2937; }
        .box { text-align: center; padding: 2rem; }
        h1 { color: {{ config('ranga.brand.color', '#e11d48') }}; }
    </style>
</head>
<body>
    <div class="box">
        <h1>{{ config('ranga.brand.name') }}</h1>
        <p>{{ __('bonus.pwa.offline_message') }}</p>
    </div>
</body>
</html>
