<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Support\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReturnRequest>
 */
class ReturnRequestFactory extends Factory
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
            'order_item_id' => null,
            'user_id' => null,
            'reason' => fake()->randomElement(['Wrong size', 'Damaged', 'Not as described']),
            'description' => fake()->sentence(),
            'images' => null,
            'status' => ReturnStatus::Pending,
            'admin_note' => null,
            'refund_method' => null,
        ];
    }
}
