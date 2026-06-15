<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Coupon;

/**
 * Persistence boundary for Coupon aggregates.
 */
interface CouponRepositoryContract
{
    /**
     * Find a coupon by its code.
     */
    public function findByCode(string $code): ?Coupon;
}
