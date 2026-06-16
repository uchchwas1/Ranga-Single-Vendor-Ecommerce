<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WhatsAppLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsAppLog>
 */
class WhatsAppLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'to' => '01'.fake()->numberBetween(3, 9).fake()->numerify('########'),
            'template' => 'order_status_update',
            'variables' => ['status' => 'shipped'],
            'status' => 'sent',
            'sent_at' => now(),
        ];
    }
}
