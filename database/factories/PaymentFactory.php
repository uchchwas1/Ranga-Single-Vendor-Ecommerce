<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Support\Enums\PaymentGateway;
use App\Support\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'user_id' => null,
            'gateway' => PaymentGateway::Cod,
            'gateway_transaction_id' => null,
            'amount' => fake()->randomFloat(2, 500, 8000),
            'currency' => 'BDT',
            'status' => PaymentStatus::Pending,
        ];
    }

    /**
     * A paid payment.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }
}
