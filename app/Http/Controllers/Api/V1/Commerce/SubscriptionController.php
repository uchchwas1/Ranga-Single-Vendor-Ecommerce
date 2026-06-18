<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\CreateSubscriptionRequest;
use App\Http\Resources\Commerce\SubscriptionResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Commerce\SubscriptionService;
use App\Support\Enums\SubscriptionInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Customer product-subscription management.
 */
class SubscriptionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SubscriptionService $subscriptions,
    ) {
    }

    /**
     * GET /profile/subscriptions — list the user's subscriptions.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        return SubscriptionResource::collection($user->subscriptions()->with('product')->latest()->get());
    }

    /**
     * POST /subscriptions — start a subscription.
     */
    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $product = Product::query()->active()->findOrFail($request->validated('product_id'));
        $variantId = $request->validated('variant_id');
        $variant = is_string($variantId) ? ProductVariant::query()->find($variantId) : null;

        $subscription = $this->subscriptions->create(
            $user,
            $product,
            $variant,
            SubscriptionInterval::from((string) $request->validated('interval')),
            (int) ($request->validated('quantity') ?? 1),
        );

        return (new SubscriptionResource($subscription))->response()->setStatusCode(201);
    }

    /**
     * POST /subscriptions/{subscription}/pause — pause a subscription.
     */
    public function pause(Request $request, Subscription $subscription): SubscriptionResource
    {
        $this->authorizeOwner($request, $subscription);

        return new SubscriptionResource($this->subscriptions->pause($subscription));
    }

    /**
     * POST /subscriptions/{subscription}/resume — resume a subscription.
     */
    public function resume(Request $request, Subscription $subscription): SubscriptionResource
    {
        $this->authorizeOwner($request, $subscription);

        return new SubscriptionResource($this->subscriptions->resume($subscription));
    }

    /**
     * DELETE /subscriptions/{subscription} — cancel a subscription.
     */
    public function destroy(Request $request, Subscription $subscription): SubscriptionResource
    {
        $this->authorizeOwner($request, $subscription);

        return new SubscriptionResource($this->subscriptions->cancel($subscription));
    }

    /**
     * Ensure the subscription belongs to the requester.
     */
    private function authorizeOwner(Request $request, Subscription $subscription): void
    {
        if ($subscription->user_id !== $request->user()?->id) {
            throw new NotFoundHttpException();
        }
    }
}
