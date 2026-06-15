<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Marketing;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyTransaction;
use App\Models\User;
use App\Services\Marketing\LoyaltyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Customer loyalty points balance and history.
 */
class LoyaltyController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly LoyaltyService $loyalty,
    ) {
    }

    /**
     * GET /profile/loyalty — points balance, tier and recent history.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $tier = $this->loyalty->currentTier($user);

        $history = $user->loyaltyTransactions()
            ->latest()
            ->limit(20)
            ->get()
            ->map(static fn (LoyaltyTransaction $tx): array => [
                'type' => $tx->type->value,
                'points' => $tx->points,
                'balance_after' => $tx->balance_after,
                'note' => $tx->note,
                'at' => $tx->created_at?->toIso8601String(),
            ])
            ->all();

        return new JsonResponse([
            'balance' => $user->loyalty_points,
            'tier' => $tier !== null ? ['name' => $tier->name, 'discount_percent' => $tier->discount_percent] : null,
            'history' => $history,
        ]);
    }
}
