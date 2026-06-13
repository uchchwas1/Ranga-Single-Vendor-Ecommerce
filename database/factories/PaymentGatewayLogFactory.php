<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentGatewayLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentGatewayLog>
 */
class PaymentGatewayLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'event' => 'initiate',
            'request' => [],
            'response' => [],
        ];
    }
}
