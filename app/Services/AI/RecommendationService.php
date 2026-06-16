<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Product recommendations: content-based on the user's purchase history,
 * with a featured-product fallback for cold-start users.
 */
class RecommendationService
{
    /**
     * Personalised recommendations for a user (or featured for guests).
     *
     * @return Collection<int, Product>
     */
    public function forUser(?User $user, int $limit = 10): Collection
    {
        if ($user === null) {
            return $this->featured($limit);
        }

        /** @var list<string> $purchasedProductIds */
        $purchasedProductIds = OrderItem::query()
            ->whereNotNull('product_id')
            ->whereHas('order', fn ($o) => $o->where('user_id', $user->id))
            ->pluck('product_id')
            ->unique()
            ->all();

        if ($purchasedProductIds === []) {
            return $this->featured($limit);
        }

        /** @var list<string> $categoryIds */
        $categoryIds = Product::query()
            ->whereIn('id', $purchasedProductIds)
            ->whereNotNull('category_id')
            ->pluck('category_id')
            ->unique()
            ->all();

        if ($categoryIds === []) {
            return $this->featured($limit);
        }

        $recommendations = Product::query()
            ->active()
            ->with(['primaryImage', 'brand'])
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $purchasedProductIds)
            ->limit($limit)
            ->get();

        return $recommendations->isNotEmpty() ? $recommendations : $this->featured($limit);
    }

    /**
     * Products related to a given product (same category).
     *
     * @return Collection<int, Product>
     */
    public function related(Product $product, int $limit = 8): Collection
    {
        return Product::query()
            ->active()
            ->with(['primaryImage', 'brand'])
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->limit($limit)
            ->get();
    }

    /**
     * Featured fallback recommendations.
     *
     * @return Collection<int, Product>
     */
    private function featured(int $limit): Collection
    {
        return Product::query()
            ->active()
            ->featured()
            ->with(['primaryImage', 'brand'])
            ->limit($limit)
            ->get();
    }
}
