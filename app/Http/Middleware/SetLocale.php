<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the request locale from the X-Locale header, a ?lang query
 * parameter, or the authenticated user's preference, restricted to the
 * supported set (Bangla default, English secondary).
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var list<string> $supported */
        $supported = (array) config('ranga.defaults.supported', ['bn', 'en']);

        $candidate = $request->header('X-Locale')
            ?? $request->query('lang')
            ?? $request->user()?->locale
            ?? (string) config('ranga.defaults.locale', 'bn');

        if (is_string($candidate) && in_array($candidate, $supported, true)) {
            app()->setLocale($candidate);
        }

        return $next($request);
    }
}
