<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\Settings\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin management of white-label platform settings.
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
     * List all settings grouped by section.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Setting::class);

        return new JsonResponse(['data' => $this->settings->all()]);
    }

    /**
     * Bulk upsert settings.
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $this->authorize('manage', Setting::class);

        /** @var list<array{group: string, key: string, value: mixed, is_public?: bool}> $items */
        $items = $request->validated('settings');

        foreach ($items as $item) {
            $this->settings->set(
                $item['group'],
                $item['key'],
                $item['value'],
                $item['is_public'] ?? null,
            );
        }

        return new JsonResponse(['message' => __('settings.updated')]);
    }
}
