<?php

declare(strict_types=1);

namespace App\Services\Commerce;

use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use Illuminate\Database\Eloquent\Collection;

/**
 * Application service for shipping method selection and rate quoting.
 *
 * Phase 3 uses a flat/per-kg rate model with a single default zone;
 * geographic zones are layered in during a later phase.
 */
class ShippingService
{
    /**
     * Active shipping methods applicable to the given order shape.
     *
     * @return Collection<int, ShippingMethod>
     */
    public function availableMethods(float $subtotal, float $weight = 0.0): Collection
    {
        return ShippingMethod::query()
            ->active()
            ->with('rates')
            ->where('min_order_amount', '<=', $subtotal)
            ->where(function ($query) use ($weight): void {
                $query->whereNull('max_weight')->orWhere('max_weight', '>=', $weight);
            })
            ->get();
    }

    /**
     * Find an active shipping method by code.
     */
    public function findByCode(string $code): ?ShippingMethod
    {
        return ShippingMethod::query()->active()->where('code', $code)->with('rates')->first();
    }

    /**
     * Quote the shipping cost for a method given subtotal and weight.
     */
    public function quote(ShippingMethod $method, float $subtotal, float $weight = 0.0): float
    {
        /** @var ShippingRate|null $rate */
        $rate = $method->rates->first();

        if ($rate === null) {
            return 0.0;
        }

        return $rate->costFor($subtotal, $weight);
    }
}
