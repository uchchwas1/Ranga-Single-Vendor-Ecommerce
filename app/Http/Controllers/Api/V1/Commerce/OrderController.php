<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\CancelOrderRequest;
use App\Http\Requests\Commerce\SubmitReturnRequest;
use App\Http\Resources\Commerce\OrderResource;
use App\Http\Resources\Commerce\ReturnRequestResource;
use App\Http\Resources\Commerce\ShipmentResource;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryContract;
use App\Services\Commerce\OrderManagementService;
use App\Services\Commerce\ReturnService;
use App\Services\Invoice\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Customer order history, detail/timeline, cancellation, returns,
 * shipment tracking and invoice download.
 */
class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly OrderRepositoryContract $orders,
        private readonly OrderManagementService $management,
        private readonly ReturnService $returns,
        private readonly InvoiceService $invoices,
    ) {
    }

    /**
     * GET /profile/orders — the authenticated user's order history.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        return OrderResource::collection($this->orders->paginateForUser($user->id));
    }

    /**
     * GET /orders/{order_number} — order detail with timeline + shipments.
     */
    public function show(Request $request, string $orderNumber): OrderResource
    {
        $order = $this->find($request, $orderNumber);
        $order->load(['statusHistories', 'shipments', 'invoice']);

        return new OrderResource($order);
    }

    /**
     * POST /orders/{order_number}/cancel — cancel an order.
     */
    public function cancel(CancelOrderRequest $request, string $orderNumber): OrderResource
    {
        // Ownership is enforced via find(); cancellability is enforced by the
        // service (422 when the order can no longer be cancelled).
        $order = $this->find($request, $orderNumber);

        /** @var User $user */
        $user = $request->user();
        $order = $this->management->cancel($order, $request->validated('reason'), $user->id);

        return new OrderResource($order->load('statusHistories'));
    }

    /**
     * POST /orders/{order_number}/return — submit a return request.
     */
    public function submitReturn(SubmitReturnRequest $request, string $orderNumber): JsonResponse
    {
        $order = $this->find($request, $orderNumber);

        /** @var array{order_item_id?: string|null, reason: string, description?: string|null, images?: array<int, string>|null} $data */
        $data = $request->validated();

        $return = $this->returns->submit($order, $request->user(), $data);

        return (new ReturnRequestResource($return))->response()->setStatusCode(201);
    }

    /**
     * GET /orders/{order_number}/tracking — shipment tracking.
     */
    public function tracking(Request $request, string $orderNumber): AnonymousResourceCollection
    {
        $order = $this->find($request, $orderNumber);

        return ShipmentResource::collection($order->shipments()->latest()->get());
    }

    /**
     * GET /orders/{order_number}/invoice — download the invoice PDF.
     */
    public function invoice(Request $request, string $orderNumber): Response
    {
        $order = $this->find($request, $orderNumber);
        $invoice = $this->invoices->generate($order);
        $contents = $this->invoices->pdfContents($invoice);

        if ($contents === null) {
            throw new NotFoundHttpException(__('commerce.invoice.unavailable'));
        }

        return new Response($contents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$invoice->invoice_number.'.pdf"',
        ]);
    }

    /**
     * Find an order by number, enforcing ownership for non-guest orders.
     */
    private function find(Request $request, string $orderNumber): Order
    {
        $order = $this->orders->findByNumber($orderNumber);

        if ($order === null) {
            throw new NotFoundHttpException(__('commerce.checkout.order_not_found'));
        }

        $this->authorizeAction($request, 'view', $order);

        return $order;
    }

    /**
     * Enforce an order policy ability for the current user.
     */
    private function authorizeAction(Request $request, string $ability, Order $order): void
    {
        $user = $request->user();

        if ($user === null || $user->cannot($ability, $order)) {
            throw new NotFoundHttpException(__('commerce.checkout.order_not_found'));
        }
    }
}
