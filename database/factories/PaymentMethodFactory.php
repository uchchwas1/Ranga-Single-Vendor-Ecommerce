<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Support\Enums\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gateway = fake()->randomElement([
            PaymentGateway::Cod,
            PaymentGateway::Sslcommerz,
            PaymentGateway::Bkash,
            PaymentGateway::Stripe,
        ]);

        return [
            'name' => $gateway->name,
            'code' => $gateway->value,
            'logo' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
            'config' => null,
        ];
    }
}
