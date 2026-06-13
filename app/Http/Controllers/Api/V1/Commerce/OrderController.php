<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Resources\Commerce\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryContract;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Order history and confirmation lookups.
 */
class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly OrderRepositoryContract $orders,
    ) {
    }

    /**
     * GET /profile/orders — the authenticated user's order history.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        return OrderResource::collection(
            $this->orders->paginateForUser($user->id),
        );
    }

    /**
     * GET /orders/{order_number} — a single order (owner or admin).
     */
    public function show(Request $request, string $orderNumber): OrderResource
    {
        $order = $this->orders->findByNumber($orderNumber);

        if ($order === null) {
            throw new NotFoundHttpException(__('commerce.checkout.order_not_found'));
        }

        if ($order->user_id !== null) {
            $this->authorizeOwner($request, $order);
        }

        return new OrderResource($order);
    }

    /**
     * Ensure the requester owns (or may administer) the order.
     */
    private function authorizeOwner(Request $request, Order $order): void
    {
        $user = $request->user();

        if ($user === null || $user->cannot('view', $order)) {
            throw new NotFoundHttpException(__('commerce.checkout.order_not_found'));
        }
    }
}
