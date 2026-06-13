<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Settings\SettingsService;
use Illuminate\Http\JsonResponse;

/**
 * Public, white-label storefront settings.
 */
class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SettingsService $settings,
    ) {
    }

    /**
     * List publicly exposable settings.
     */
    public function __invoke(): JsonResponse
    {
        return new JsonResponse(['data' => $this->settings->publicSettings()]);
    }
}
