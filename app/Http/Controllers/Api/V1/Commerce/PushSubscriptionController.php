<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\StorePushSubscriptionRequest;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Web-push subscription management for the authenticated user.
 */
class PushSubscriptionController extends Controller
{
    /**
     * POST /profile/push-subscriptions — register (or refresh) a device.
     */
    public function store(StorePushSubscriptionRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        PushSubscription::query()->updateOrCreate(
            ['endpoint' => (string) $request->validated('endpoint')],
            [
                'user_id' => $user->id,
                'p256dh' => $request->validated('p256dh'),
                'auth' => $request->validated('auth'),
                'device_type' => $request->validated('device_type'),
            ],
        );

        return new JsonResponse(['message' => __('notifications.push_subscribed')], 201);
    }

    /**
     * DELETE /profile/push-subscriptions — remove a device by endpoint.
     */
    public function destroy(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $endpoint = $request->input('endpoint');

        if (is_string($endpoint)) {
            $user->pushSubscriptions()->where('endpoint', $endpoint)->delete();
        }

        return new JsonResponse(['message' => __('notifications.push_unsubscribed')]);
    }
}
