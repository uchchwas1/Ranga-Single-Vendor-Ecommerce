<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustLoyaltyRequest;
use App\Http\Resources\Admin\CustomerResource;
use App\Models\User;
use App\Services\Customers\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin customer management.
 */
class AdminCustomerController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly CustomerService $customers,
    ) {
    }

    /**
     * GET /admin/customers — list customers with search/segment filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = $request->query('search');
        $segment = $request->query('segment');

        return CustomerResource::collection($this->customers->paginate(
            is_string($search) ? $search : null,
            is_string($segment) ? $segment : null,
        ));
    }

    /**
     * GET /admin/customers/{user} — customer detail.
     */
    public function show(User $user): CustomerResource
    {
        return new CustomerResource($this->customers->detail($user));
    }

    /**
     * POST /admin/customers/{user}/loyalty — adjust loyalty points.
     */
    public function adjustLoyalty(AdjustLoyaltyRequest $request, User $user): JsonResponse
    {
        $transaction = $this->customers->adjustLoyalty(
            $user,
            (int) $request->validated('points'),
            $request->validated('note'),
        );

        return new JsonResponse([
            'message' => __('cms.customer.loyalty_adjusted'),
            'balance' => $transaction->balance_after,
        ]);
    }
}
