<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Support\Enums\InventoryLogType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryLog>
 */
class InventoryLogFactory extends Factory
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
            'type' => InventoryLogType::Adjustment,
            'quantity_before' => 10,
            'quantity_after' => 8,
            'reference_type' => null,
            'reference_id' => null,
            'note' => fake()->sentence(),
            'created_by' => null,
        ];
    }
}
