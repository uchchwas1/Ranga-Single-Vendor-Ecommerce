<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a user.
 *
 * @mixin User
 */
class UserResource extends JsonResource
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
            'avatar' => $this->avatar,
            'locale' => $this->locale,
            'timezone' => $this->timezone,
            'referral_code' => $this->referral_code,
            'loyalty_points' => $this->loyalty_points,
            'two_factor_enabled' => $this->hasTwoFactorEnabled(),
            'email_verified' => $this->email_verified_at !== null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
