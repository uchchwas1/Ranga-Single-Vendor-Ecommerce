<?php

declare(strict_types=1);

namespace App\Http\Resources\Commerce;

use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a return request.
 *
 * @mixin ReturnRequest
 */
class ReturnRequestResource extends JsonResource
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
            'order_id' => $this->order_id,
            'order_item_id' => $this->order_item_id,
            'reason' => $this->reason,
            'description' => $this->description,
            'images' => $this->images,
            'status' => $this->status->value,
            'refund_method' => $this->refund_method?->value,
            'admin_note' => $this->admin_note,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
