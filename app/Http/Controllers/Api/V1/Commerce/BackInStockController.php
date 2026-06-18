<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\SubscribeBackInStockRequest;
use App\Models\ProductVariant;
use App\Services\Commerce\BackInStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Back-in-stock watch registration.
 */
class BackInStockController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly BackInStockService $backInStock,
    ) {
    }

    /**
     * POST /back-in-stock — register a notify-me request for a variant.
     *
     * @throws ValidationException
     */
    public function store(SubscribeBackInStockRequest $request): JsonResponse
    {
        $user = $request->user();
        $email = $request->validated('email') ?? $user?->email;

        if (! is_string($email)) {
            throw ValidationException::withMessages(['email' => [__('bonus.back_in_stock.email_required')]]);
        }

        /** @var ProductVariant $variant */
        $variant = ProductVariant::query()->findOrFail($request->validated('variant_id'));

        $this->backInStock->subscribe($variant, $email, $user);

        return new JsonResponse(['message' => __('bonus.back_in_stock.subscribed')], 201);
    }
}
