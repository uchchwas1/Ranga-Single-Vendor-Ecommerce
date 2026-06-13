<?php

declare(strict_types=1);

namespace App\Support\Dto;

/**
 * Immutable set of catalogue listing filters parsed from a request.
 */
final readonly class ProductFilters
{
    /**
     * @param  list<string>  $attributeValueIds
     */
    public function __construct(
        public ?string $categoryId = null,
        public ?string $brandId = null,
        public ?float $minPrice = null,
        public ?float $maxPrice = null,
        public ?int $minRating = null,
        public bool $featuredOnly = false,
        public array $attributeValueIds = [],
        public string $sort = 'latest',
        public int $perPage = 24,
    ) {
    }

    /**
     * Build the DTO from a validated request payload.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $attributeValues = $data['attribute_values'] ?? [];

        return new self(
            categoryId: self::stringOrNull($data['category'] ?? null),
            brandId: self::stringOrNull($data['brand'] ?? null),
            minPrice: isset($data['min_price']) ? (float) $data['min_price'] : null,
            maxPrice: isset($data['max_price']) ? (float) $data['max_price'] : null,
            minRating: isset($data['rating']) ? (int) $data['rating'] : null,
            featuredOnly: (bool) ($data['featured'] ?? false),
            attributeValueIds: is_array($attributeValues) ? array_values(array_map('strval', $attributeValues)) : [],
            sort: self::stringOrNull($data['sort'] ?? null) ?? 'latest',
            perPage: isset($data['per_page']) ? min(60, max(1, (int) $data['per_page'])) : 24,
        );
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
