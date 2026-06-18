<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Exposes the supported storefront locales.
 */
class LocaleController extends Controller
{
    /**
     * GET /i18n/locales — supported locales and the active one.
     */
    public function index(): JsonResponse
    {
        return new JsonResponse([
            'current' => app()->getLocale(),
            'default' => (string) config('ranga.defaults.locale', 'bn'),
            'supported' => (array) config('ranga.defaults.supported', ['bn', 'en']),
        ]);
    }
}
