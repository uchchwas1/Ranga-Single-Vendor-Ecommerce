<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A loyalty tier unlocked at a points threshold.
 *
 * @property string $id
 * @property string $name
 * @property int $min_points
 * @property string $discount_percent
 * @property array<string, mixed>|null $perks
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class LoyaltyTier extends Model
{
    /** @use HasFactory<\Database\Factories\LoyaltyTierFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'min_points',
        'discount_percent',
        'perks',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_points' => 'integer',
            'discount_percent' => 'decimal:2',
            'perks' => 'array',
        ];
    }
}
