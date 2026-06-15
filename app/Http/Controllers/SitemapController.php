<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Seo\SeoService;
use Illuminate\Http\Response;

/**
 * Serves the dynamic XML sitemap and robots.txt.
 */
class SitemapController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SeoService $seo,
    ) {
    }

    /**
     * GET /sitemap.xml — the dynamic sitemap.
     */
    public function sitemap(): Response
    {
        return new Response($this->seo->sitemapXml(), 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * GET /robots.txt — the dynamic robots file.
     */
    public function robots(): Response
    {
        return new Response($this->seo->robotsTxt(), 200, ['Content-Type' => 'text/plain']);
    }
}
