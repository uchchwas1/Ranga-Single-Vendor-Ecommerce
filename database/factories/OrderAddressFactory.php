<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderAddress;
use App\Support\Enums\AddressType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderAddress>
 */
class OrderAddressFactory extends Factory
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
            'type' => AddressType::Shipping,
            'name' => fake()->name(),
            'phone' => '01'.fake()->numberBetween(3, 9).fake()->numerify('########'),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => null,
            'city' => fake()->city(),
            'state' => null,
            'postal_code' => fake()->postcode(),
            'country_code' => 'BD',
        ];
    }
}
