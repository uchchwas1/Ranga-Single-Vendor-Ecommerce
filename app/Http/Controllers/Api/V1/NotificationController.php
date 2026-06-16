<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Authenticated user notification inbox.
 */
class NotificationController extends Controller
{
    /**
     * GET /notifications — list the user's notifications.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        return NotificationResource::collection($user->notifications()->paginate(20));
    }

    /**
     * POST /notifications/{id}/read — mark one notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->notifications()->whereKey($id)->update(['read_at' => now()]);

        return new JsonResponse(['message' => __('notifications.marked_read')]);
    }

    /**
     * POST /notifications/read-all — mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return new JsonResponse(['message' => __('notifications.marked_all_read')]);
    }
}
