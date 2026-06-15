<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Coupon;
use App\Repositories\Contracts\CouponRepositoryContract;

/**
 * Eloquent implementation of the coupon repository.
 */
class EloquentCouponRepository implements CouponRepositoryContract
{
    /**
     * Find a coupon by its code.
     */
    public function findByCode(string $code): ?Coupon
    {
        return Coupon::query()->where('code', $code)->first();
    }
}
