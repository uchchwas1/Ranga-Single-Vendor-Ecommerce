<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Popup;
use App\Support\Enums\PopupTrigger;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Popup>
 */
class PopupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'content' => fake()->sentence(),
            'trigger_type' => PopupTrigger::Delay,
            'trigger_delay' => 5,
            'show_once' => true,
            'is_active' => true,
        ];
    }
}
