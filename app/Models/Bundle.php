<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A product bundle sold at a combined price/discount.
 *
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $price
 * @property string $discount_percent
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Bundle extends Model
{
    /** @use HasFactory<\Database\Factories\BundleFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'discount_percent',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The products included in the bundle.
     *
     * @return HasMany<BundleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BundleItem::class);
    }

    /**
     * Scope to active bundles.
     *
     * @param  Builder<Bundle>  $query
     * @return Builder<Bundle>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Use the slug for route-model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
