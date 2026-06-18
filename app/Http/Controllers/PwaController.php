<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Media\PwaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Serves PWA assets: manifest, service worker and offline page.
 */
class PwaController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly PwaService $pwa,
    ) {
    }

    /**
     * GET /manifest.json — the web app manifest.
     */
    public function manifest(): JsonResponse
    {
        return new JsonResponse($this->pwa->manifest(), 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }

    /**
     * GET /sw.js — the service worker script.
     */
    public function serviceWorker(): Response
    {
        return new Response($this->pwa->serviceWorker(), 200, [
            'Content-Type' => 'application/javascript',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    /**
     * GET /offline — the offline fallback page.
     */
    public function offline(): View
    {
        return view('pwa.offline');
    }
}
