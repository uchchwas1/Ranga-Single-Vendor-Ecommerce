<?php

declare(strict_types=1);

namespace App\Http\Resources\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Admin representation of a customer.
 *
 * @mixin User
 */
class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'loyalty_points' => $this->loyalty_points,
            'is_active' => $this->is_active,
            'orders_count' => $this->whenCounted('orders'),
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'recent_orders' => $this->whenLoaded('orders', fn () => $this->orders->map(static fn ($o): array => [
                'order_number' => $o->order_number,
                'status' => $o->status->value,
                'total' => $o->total,
            ])->all()),
        ];
    }
}
