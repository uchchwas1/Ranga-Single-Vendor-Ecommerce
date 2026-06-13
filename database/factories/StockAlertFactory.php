<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\StockAlert;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;

/**
 * @extends Factory<StockAlert>
 */
class StockAlertFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inventory_id' => Inventory::factory(),
            'notified_at' => Date::now(),
        ];
    }
}
