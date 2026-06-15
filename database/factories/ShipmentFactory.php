<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Shipment;
use App\Support\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipment>
 */
class ShipmentFactory extends Factory
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
            'tracking_number' => mb_strtoupper(fake()->bothify('??########')),
            'carrier' => fake()->randomElement(['Pathao', 'RedX', 'Steadfast']),
            'carrier_url' => 'https://track.example.com/{tracking}',
            'status' => ShipmentStatus::Shipped,
            'shipped_at' => now(),
        ];
    }
}
