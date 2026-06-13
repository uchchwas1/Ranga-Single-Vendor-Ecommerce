<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use App\Support\Enums\PaymentStatus;
use App\Support\Enums\ShippingStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 8000);
        $shipping = 60;

        return [
            'order_number' => 'RNG-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'user_id' => User::factory(),
            'guest_email' => null,
            'status' => OrderStatus::Pending,
            'payment_status' => PaymentStatus::Pending,
            'shipping_status' => ShippingStatus::Pending,
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'shipping_amount' => $shipping,
            'tax_amount' => 0,
            'total' => $subtotal + $shipping,
            'currency' => 'BDT',
        ];
    }

    /**
     * A guest order.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
            'guest_email' => fake()->safeEmail(),
        ]);
    }
}
