<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Coupon;
use App\Support\Enums\CouponType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => Str::upper(Str::random(8)),
            'type' => CouponType::Percent,
            'value' => 10,
            'min_order_amount' => 0,
            'max_discount_amount' => null,
            'usage_limit' => null,
            'used_count' => 0,
            'user_limit' => null,
            'product_ids' => null,
            'category_ids' => null,
            'starts_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ];
    }

    /**
     * A fixed-amount coupon.
     */
    public function fixed(float $amount = 100): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CouponType::Fixed,
            'value' => $amount,
        ]);
    }

    /**
     * A free-shipping coupon.
     */
    public function freeShipping(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => CouponType::FreeShipping,
            'value' => 0,
        ]);
    }

    /**
     * An expired coupon.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->subDay(),
        ]);
    }
}
