<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Models\FlashSale;
use App\Models\FlashSaleItem;
use Illuminate\Database\Eloquent\Collection;

/**
 * Application service for flash sales and sale-price resolution.
 */
class FlashSaleService
{
    /**
     * Currently live flash sales with their items.
     *
     * @return Collection<int, FlashSale>
     */
    public function active(): Collection
    {
        return FlashSale::query()
            ->live()
            ->with(['items.product.primaryImage'])
            ->get();
    }

    /**
     * The active flash-sale price for a product/variant, if any.
     */
    public function priceFor(string $productId, ?string $variantId = null): ?float
    {
        /** @var FlashSaleItem|null $item */
        $item = FlashSaleItem::query()
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->whereHas('flashSale', fn ($q) => $q->live())
            ->first();

        if ($item === null || ! $item->hasStock()) {
            return null;
        }

        return (float) $item->sale_price;
    }
}
