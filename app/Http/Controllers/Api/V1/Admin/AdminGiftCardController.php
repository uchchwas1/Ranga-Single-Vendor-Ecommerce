<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IssueGiftCardRequest;
use App\Http\Resources\Marketing\GiftCardResource;
use App\Models\GiftCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Admin gift-card issuance.
 */
class AdminGiftCardController extends Controller
{
    /**
     * POST /admin/gift-cards — issue a new gift card.
     */
    public function store(IssueGiftCardRequest $request): JsonResponse
    {
        $balance = (float) $request->validated('initial_balance');

        /** @var GiftCard $card */
        $card = GiftCard::query()->create([
            'code' => 'GC-'.Str::upper(Str::random(12)),
            'initial_balance' => $balance,
            'current_balance' => $balance,
            'currency' => $request->validated('currency') ?? (string) config('ranga.defaults.currency', 'BDT'),
            'expires_at' => $request->validated('expires_at'),
            'is_active' => true,
            'issued_to_user_id' => $request->validated('issued_to_user_id'),
        ]);

        return (new GiftCardResource($card))->response()->setStatusCode(201);
    }
}
