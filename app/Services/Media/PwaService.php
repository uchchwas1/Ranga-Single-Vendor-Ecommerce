<?php

declare(strict_types=1);

namespace App\Services\Media;

/**
 * Builds the white-label PWA manifest and service worker.
 */
class PwaService
{
    /**
     * The web app manifest, themed from brand config.
     *
     * @return array<string, mixed>
     */
    public function manifest(): array
    {
        $name = (string) config('ranga.brand.name', 'Ranga');
        $color = (string) config('ranga.brand.color', '#e11d48');

        return [
            'name' => $name,
            'short_name' => $name,
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => $color,
            'icons' => [
                ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png'],
            ],
        ];
    }

    /**
     * The service worker script (cache-first shell + offline fallback).
     */
    public function serviceWorker(): string
    {
        $cache = 'ranga-shell-v1';

        return <<<JS
        const CACHE = '{$cache}';
        const OFFLINE_URL = '/offline';

        self.addEventListener('install', (event) => {
            event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll([OFFLINE_URL])));
            self.skipWaiting();
        });

        self.addEventListener('activate', (event) => self.clients.claim());

        self.addEventListener('fetch', (event) => {
            if (event.request.mode === 'navigate') {
                event.respondWith(fetch(event.request).catch(() => caches.match(OFFLINE_URL)));
            }
        });
        JS;
    }
}
