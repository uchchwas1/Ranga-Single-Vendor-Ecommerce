<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DecideReturnRequest;
use App\Http\Resources\Commerce\ReturnRequestResource;
use App\Models\ReturnRequest;
use App\Services\Commerce\RefundService;
use App\Services\Commerce\ReturnService;
use App\Support\Enums\RefundMethod;
use App\Support\Enums\ReturnStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin handling of return requests and gateway refunds.
 */
class AdminReturnController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ReturnService $returns,
        private readonly RefundService $refunds,
    ) {
    }

    /**
     * GET /admin/returns — pending return requests queue.
     */
    public function index(): AnonymousResourceCollection
    {
        $requests = ReturnRequest::query()
            ->where('status', ReturnStatus::Pending->value)
            ->with(['order', 'orderItem'])
            ->latest()
            ->paginate(20);

        return ReturnRequestResource::collection($requests);
    }

    /**
     * POST /admin/returns/{return}/approve — approve and issue a refund.
     */
    public function approve(DecideReturnRequest $request, ReturnRequest $return): JsonResponse
    {
        $method = $request->validated('refund_method') !== null
            ? RefundMethod::from((string) $request->validated('refund_method'))
            : RefundMethod::OriginalPayment;

        $this->returns->approve($return, $method, $request->validated('admin_note'));

        $amount = $request->validated('amount');
        $refund = $this->refunds->processForReturn($return, $amount !== null ? (float) $amount : null);

        return new JsonResponse([
            'data' => new ReturnRequestResource($return->refresh()),
            'refund' => [
                'amount' => $refund->amount,
                'status' => $refund->status->value,
            ],
        ]);
    }

    /**
     * POST /admin/returns/{return}/reject — reject a return.
     */
    public function reject(DecideReturnRequest $request, ReturnRequest $return): ReturnRequestResource
    {
        $this->returns->reject($return, $request->validated('admin_note'));

        return new ReturnRequestResource($return->refresh());
    }
}
