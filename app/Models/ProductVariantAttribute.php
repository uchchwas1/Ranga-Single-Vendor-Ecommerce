<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot linking a product variant to a specific attribute value
 * (e.g. variant X has Colour = Red).
 *
 * Uses a ULID primary key, so it is a concrete pivot model rather than
 * an anonymous pivot — associations must be created through this model.
 *
 * @property string $id
 * @property string $variant_id
 * @property string $attribute_id
 * @property string $attribute_value_id
 */
class ProductVariantAttribute extends Pivot
{
    use HasUlids;

    /**
     * This pivot has its own incrementing-free ULID key.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The table associated with the pivot.
     *
     * @var string
     */
    protected $table = 'product_variant_attributes';

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'variant_id',
        'attribute_id',
        'attribute_value_id',
    ];
}
