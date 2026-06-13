<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\Product;
use App\Models\ProductComparison;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * Application service for the product comparison list.
 */
class ComparisonService
{
    private const int MAX_ITEMS = 4;

    /**
     * Resolve (or create) the comparison list for the actor.
     */
    public function resolve(?User $user, ?string $token): ProductComparison
    {
        if ($user !== null) {
            /** @var ProductComparison $comparison */
            $comparison = ProductComparison::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['product_ids' => [], 'created_at' => Date::now()],
            );

            return $comparison;
        }

        /** @var ProductComparison $comparison */
        $comparison = ProductComparison::query()->firstOrCreate(
            ['session_id' => $token ?? (string) Str::ulid(), 'user_id' => null],
            ['product_ids' => []],
        );

        return $comparison;
    }

    /**
     * The products currently in the comparison list.
     *
     * @return Collection<int, Product>
     */
    public function products(ProductComparison $comparison): Collection
    {
        $ids = $comparison->product_ids;

        if ($ids === []) {
            /** @var Collection<int, Product> $empty */
            $empty = Product::query()->whereRaw('1 = 0')->get();

            return $empty;
        }

        return Product::query()
            ->active()
            ->with(['primaryImage', 'brand'])
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * Add a product to the comparison list (capped, de-duplicated).
     */
    public function add(ProductComparison $comparison, string $productId): ProductComparison
    {
        $ids = $comparison->product_ids;

        if (! in_array($productId, $ids, true) && count($ids) < self::MAX_ITEMS) {
            $ids[] = $productId;
            $comparison->update(['product_ids' => array_values($ids)]);
        }

        return $comparison;
    }

    /**
     * Remove a product from the comparison list.
     */
    public function remove(ProductComparison $comparison, string $productId): ProductComparison
    {
        $ids = array_values(array_filter(
            $comparison->product_ids,
            static fn (string $id): bool => $id !== $productId,
        ));

        $comparison->update(['product_ids' => $ids]);

        return $comparison;
    }
}
