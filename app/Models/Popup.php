<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\PopupTrigger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A marketing popup shown on the storefront.
 *
 * @property string $id
 * @property string $name
 * @property string|null $content
 * @property PopupTrigger $trigger_type
 * @property int $trigger_delay
 * @property bool $show_once
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Popup extends Model
{
    /** @use HasFactory<\Database\Factories\PopupFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'content',
        'trigger_type',
        'trigger_delay',
        'show_once',
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
            'trigger_type' => PopupTrigger::class,
            'trigger_delay' => 'integer',
            'show_once' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope to active popups.
     *
     * @param  Builder<Popup>  $query
     * @return Builder<Popup>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
