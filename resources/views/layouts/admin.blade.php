<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin.title', ['brand' => config('ranga.brand.name')]))</title>
    <style>:root { --brand-color: {{ config('ranga.brand.color') }}; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-50">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-gray-900 text-gray-100 hidden md:block">
            <div class="p-4 text-lg font-bold" style="color: var(--brand-color)">
                {{ config('ranga.brand.name') }}
            </div>
            <nav class="px-2 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block rounded px-3 py-2 hover:bg-gray-800">
                    {{ __('Dashboard') }}
                </a>
            </nav>
        </aside>
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>
</body>
</html>
