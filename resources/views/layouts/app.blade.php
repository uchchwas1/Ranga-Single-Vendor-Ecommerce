<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('ranga.brand.name'))</title>
    <meta name="description" content="@yield('meta_description', config('ranga.brand.tagline'))">
    <style>:root { --brand-color: {{ config('ranga.brand.color') }}; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-white text-gray-900 dark:bg-gray-950 dark:text-gray-50">
    <header class="border-b border-gray-100 dark:border-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xl font-bold" style="color: var(--brand-color)">
                {{ config('ranga.brand.name') }}
            </a>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="border-t border-gray-100 dark:border-gray-800 mt-16">
        <div class="mx-auto max-w-7xl px-4 py-8 text-sm text-gray-500">
            © {{ now()->format('Y') }} {{ config('ranga.brand.name') }} — {{ config('ranga.brand.tagline') }}
        </div>
    </footer>
</body>
</html>
