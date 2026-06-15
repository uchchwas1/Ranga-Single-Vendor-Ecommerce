<?php

declare(strict_types=1);

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', static function () {
    return view('frontend.home');
})->name('home');

// Dynamic SEO files.
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
