<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductComparison;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductComparison>
 */
class ProductComparisonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'session_id' => (string) Str::ulid(),
            'product_ids' => [],
        ];
    }
}
