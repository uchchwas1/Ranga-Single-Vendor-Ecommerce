<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Banner;
use App\Support\Enums\BannerPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Banner>
 */
class BannerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'image' => 'banners/'.fake()->uuid().'.webp',
            'mobile_image' => null,
            'link' => '/products',
            'position' => BannerPosition::Hero,
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
