<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ProductObserver;
use App\Support\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Date;
use Laravel\Scout\Searchable;

/**
 * A catalogue product. Variants carry the sellable price and stock.
 *
 * @property string $id
 * @property string|null $category_id
 * @property string|null $brand_id
 * @property string $name
 * @property string $slug
 * @property string $sku
 * @property string|null $barcode
 * @property string|null $short_description
 * @property string|null $description
 * @property array<string, mixed>|null $specifications
 * @property array<int, array<string, string>>|null $faqs
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @property ProductStatus $status
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property bool $is_featured
 * @property bool $is_digital
 * @property float|null $weight
 * @property string $weight_unit
 * @property array<string, mixed>|null $dimensions
 * @property string|null $video_url
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
#[ObservedBy(ProductObserver::class)]
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, HasUlids, Searchable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'short_description',
        'description',
        'specifications',
        'faqs',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'published_at',
        'is_featured',
        'is_digital',
        'weight',
        'weight_unit',
        'dimensions',
        'video_url',
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
            'specifications' => 'array',
            'faqs' => 'array',
            'dimensions' => 'array',
            'status' => ProductStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_digital' => 'boolean',
            'weight' => 'decimal:3',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The category the product belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The brand the product belongs to.
     *
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Purchasable variants of the product.
     *
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Gallery images.
     *
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * The primary gallery image.
     *
     * @return HasOne<ProductImage, $this>
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /**
     * Product videos.
     *
     * @return HasMany<ProductVideo, $this>
     */
    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class)->orderBy('sort_order');
    }

    /**
     * Free-text tags.
     *
     * @return HasMany<ProductTag, $this>
     */
    public function tags(): HasMany
    {
        return $this->hasMany(ProductTag::class);
    }

    /**
     * Inventory rows across warehouses/variants.
     *
     * @return HasMany<Inventory, $this>
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    /**
     * Scope the query to publicly purchasable products.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', ProductStatus::Active->value)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', Date::now());
            });
    }

    /**
     * Scope the query to featured products.
     *
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * The lowest active variant price, if any.
     */
    public function priceFrom(): ?float
    {
        $price = $this->variants()
            ->where('is_active', true)
            ->min('price');

        return $price !== null ? (float) $price : null;
    }

    /**
     * Use the slug for route-model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Only index publicly visible products in the search engine.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    /**
     * The data indexed by Laravel Scout.
     *
     * Only real product columns are included so the Scout "database"
     * driver (used in tests) can query them directly.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'meta_keywords' => $this->meta_keywords,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'status' => $this->status?->value,
            'is_featured' => (int) $this->is_featured,
            'sort_order' => $this->sort_order,
        ];
    }
}
