<?php

declare(strict_types=1);

namespace App\Http\Resources\Commerce;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a product subscription.
 *
 * @mixin Subscription
 */
class SubscriptionResource extends JsonResource
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
            'product_id' => $this->product_id,
            'variant_id' => $this->variant_id,
            'interval' => $this->interval->value,
            'status' => $this->status->value,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'next_billing_at' => $this->next_billing_at?->toIso8601String(),
            'product' => $this->whenLoaded('product', fn () => $this->product?->name),
        ];
    }
}
