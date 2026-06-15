<?php

declare(strict_types=1);

namespace App\Http\Resources\Commerce;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a shipment for tracking.
 *
 * @mixin Shipment
 */
class ShipmentResource extends JsonResource
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
            'status' => $this->status->value,
            'carrier' => $this->carrier,
            'tracking_number' => $this->tracking_number,
            'tracking_url' => $this->trackingUrl(),
            'shipped_at' => $this->shipped_at?->toIso8601String(),
            'estimated_delivery' => $this->estimated_delivery?->toDateString(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
        ];
    }
}
