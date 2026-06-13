<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\AttributeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A product attribute (e.g. Colour, Size) used to build variants.
 *
 * @property string $id
 * @property string $name
 * @property AttributeType $type
 * @property bool $is_filterable
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Attribute extends Model
{
    /** @use HasFactory<\Database\Factories\AttributeFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'type',
        'is_filterable',
        'sort_order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AttributeType::class,
            'is_filterable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Selectable values for this attribute.
     *
     * @return HasMany<AttributeValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('sort_order');
    }

    /**
     * Scope the query to filterable attributes.
     *
     * @param  Builder<Attribute>  $query
     * @return Builder<Attribute>
     */
    public function scopeFilterable(Builder $query): Builder
    {
        return $query->where('is_filterable', true);
    }
}
