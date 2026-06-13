<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('ranga.brand.name'))</title>
    <style>:root { --brand-color: {{ config('ranga.brand.color') }}; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50 dark:bg-gray-950 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md p-6">
        @yield('content')
    </div>
</body>
</html>
