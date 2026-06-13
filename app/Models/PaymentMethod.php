<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An available payment method (gateway configuration).
 *
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $logo
 * @property bool $is_active
 * @property int $sort_order
 * @property array<string, mixed>|null $config
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PaymentMethod extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentMethodFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'code',
        'logo',
        'is_active',
        'sort_order',
        'config',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            // Gateway credentials are encrypted at rest (AES-256).
            'config' => 'encrypted:array',
        ];
    }

    /**
     * Scope to active methods, ordered for display.
     *
     * @param  Builder<PaymentMethod>  $query
     * @return Builder<PaymentMethod>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
