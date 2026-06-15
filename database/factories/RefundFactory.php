<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Refund;
use App\Support\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Refund>
 */
class RefundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'return_request_id' => null,
            'order_id' => Order::factory(),
            'payment_id' => null,
            'amount' => fake()->randomFloat(2, 100, 5000),
            'status' => RefundStatus::Pending,
            'gateway_refund_id' => null,
            'processed_at' => null,
        ];
    }
}
