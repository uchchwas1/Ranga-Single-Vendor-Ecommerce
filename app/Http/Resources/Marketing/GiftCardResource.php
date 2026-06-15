<?php

declare(strict_types=1);

namespace App\Http\Resources\Marketing;

use App\Models\GiftCard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a gift card.
 *
 * @mixin GiftCard
 */
class GiftCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'initial_balance' => $this->initial_balance,
            'current_balance' => $this->current_balance,
            'currency' => $this->currency,
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_active' => $this->is_active,
        ];
    }
}
