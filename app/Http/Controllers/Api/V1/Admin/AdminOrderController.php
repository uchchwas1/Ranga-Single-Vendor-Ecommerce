<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ChangeOrderStatusRequest;
use App\Http\Requests\Admin\CreateShipmentRequest;
use App\Http\Resources\Commerce\OrderResource;
use App\Http\Resources\Commerce\ShipmentResource;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryContract;
use App\Services\Commerce\OrderManagementService;
use App\Services\Commerce\ShipmentService;
use App\Support\Enums\OrderStatus;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Admin order management: status transitions and shipment dispatch.
 */
class AdminOrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly OrderRepositoryContract $orders,
        private readonly OrderManagementService $management,
        private readonly ShipmentService $shipments,
    ) {
    }

    /**
     * PUT /admin/orders/{order_number}/status — transition an order.
     */
    public function changeStatus(ChangeOrderStatusRequest $request, string $orderNumber): OrderResource
    {
        $order = $this->find($orderNumber);

        /** @var User $actor */
        $actor = $request->user();

        $order = $this->management->changeStatus(
            $order,
            OrderStatus::from((string) $request->validated('status')),
            $request->validated('comment'),
            (bool) ($request->validated('notify_customer') ?? false),
            $actor->id,
        );

        return new OrderResource($order->load('statusHistories'));
    }

    /**
     * POST /admin/orders/{order_number}/shipments — dispatch a shipment.
     */
    public function ship(CreateShipmentRequest $request, string $orderNumber): JsonResponse
    {
        $order = $this->find($orderNumber);

        /** @var User $actor */
        $actor = $request->user();

        /** @var array{tracking_number?: string|null, carrier?: string|null, carrier_url?: string|null, estimated_delivery?: string|null} $data */
        $data = $request->validated();

        $shipment = $this->shipments->ship($order, $data, $actor->id);

        return (new ShipmentResource($shipment))->response()->setStatusCode(201);
    }

    /**
     * Resolve an order by number for admin use.
     */
    private function find(string $orderNumber): Order
    {
        $order = $this->orders->findByNumber($orderNumber);

        if ($order === null) {
            throw new NotFoundHttpException(__('commerce.checkout.order_not_found'));
        }

        return $order;
    }
}
