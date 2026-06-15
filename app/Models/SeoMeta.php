<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * SEO metadata attached polymorphically to a model.
 *
 * @property string $id
 * @property string $model_type
 * @property string $model_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $keywords
 * @property string|null $og_image
 * @property array<string, mixed>|null $schema_markup
 * @property string|null $canonical_url
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SeoMeta extends Model
{
    /** @use HasFactory<\Database\Factories\SeoMetaFactory> */
    use HasFactory, HasUlids;

    /**
     * The database table.
     *
     * @var string
     */
    protected $table = 'seo_meta';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'model_type',
        'model_id',
        'title',
        'description',
        'keywords',
        'og_image',
        'schema_markup',
        'canonical_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schema_markup' => 'array',
        ];
    }

    /**
     * The owning model.
     *
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'model_type', 'model_id');
    }
}
