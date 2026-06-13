<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A configurable white-label platform setting.
 *
 * @property string $id
 * @property string $group
 * @property string $key
 * @property mixed $value
 * @property bool $is_public
 */
class Setting extends Model
{
    /** @use HasFactory<\Database\Factories\SettingFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'group',
        'key',
        'value',
        'is_public',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
            'is_public' => 'boolean',
        ];
    }

    /**
     * Scope the query to publicly exposable settings.
     *
     * @param  Builder<Setting>  $query
     * @return Builder<Setting>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }
}
