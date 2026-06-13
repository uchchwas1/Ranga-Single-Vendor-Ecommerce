<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Support\Enums\PaymentGateway;
use Illuminate\Database\Seeder;

/**
 * Seeds shipping methods (with rates) and active payment methods.
 */
class CommerceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedShipping();
        $this->seedPaymentMethods();
    }

    /**
     * Default shipping methods and their flat rates.
     */
    private function seedShipping(): void
    {
        $methods = [
            ['name' => 'Inside Dhaka', 'code' => 'inside-dhaka', 'carrier' => 'Pathao', 'base' => 60.0, 'free' => 3000.0],
            ['name' => 'Outside Dhaka', 'code' => 'outside-dhaka', 'carrier' => 'RedX', 'base' => 120.0, 'free' => 5000.0],
        ];

        foreach ($methods as $index => $data) {
            /** @var ShippingMethod $method */
            $method = ShippingMethod::query()->updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'carrier' => $data['carrier'],
                    'min_order_amount' => 0,
                    'is_active' => true,
                    'sort_order' => $index,
                ],
            );

            $method->rates()->updateOrCreate(
                ['shipping_zone_id' => null],
                [
                    'base_rate' => $data['base'],
                    'per_kg_rate' => 0,
                    'free_above_amount' => $data['free'],
                    'estimated_days_min' => 1,
                    'estimated_days_max' => 5,
                ],
            );
        }
    }

    /**
     * Active payment methods available at checkout.
     */
    private function seedPaymentMethods(): void
    {
        $gateways = [
            PaymentGateway::Cod,
            PaymentGateway::Sslcommerz,
            PaymentGateway::Bkash,
            PaymentGateway::Stripe,
        ];

        foreach ($gateways as $index => $gateway) {
            PaymentMethod::query()->updateOrCreate(
                ['code' => $gateway->value],
                [
                    'name' => $gateway->name,
                    'is_active' => true,
                    'sort_order' => $index,
                ],
            );
        }
    }
}
