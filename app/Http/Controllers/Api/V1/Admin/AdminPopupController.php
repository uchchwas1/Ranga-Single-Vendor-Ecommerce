<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreatePopupRequest;
use App\Http\Resources\Cms\PopupResource;
use App\Models\Popup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin marketing-popup management.
 */
class AdminPopupController extends Controller
{
    /**
     * GET /admin/popups — list popups.
     */
    public function index(): AnonymousResourceCollection
    {
        return PopupResource::collection(Popup::query()->latest()->paginate(20));
    }

    /**
     * POST /admin/popups — create a popup.
     */
    public function store(CreatePopupRequest $request): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->validated();

        /** @var Popup $popup */
        $popup = Popup::query()->create($data);

        return (new PopupResource($popup))->response()->setStatusCode(201);
    }

    /**
     * DELETE /admin/popups/{popup} — delete a popup.
     */
    public function destroy(Popup $popup): JsonResponse
    {
        $popup->delete();

        return new JsonResponse(['message' => __('cms.popup.deleted')]);
    }
}
