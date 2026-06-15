<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCouponRequest;
use App\Http\Resources\Marketing\CouponResource;
use App\Models\Coupon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin coupon management.
 */
class AdminCouponController extends Controller
{
    /**
     * GET /admin/coupons — list coupons.
     */
    public function index(): AnonymousResourceCollection
    {
        return CouponResource::collection(Coupon::query()->latest()->paginate(20));
    }

    /**
     * POST /admin/coupons — create a coupon.
     */
    public function store(CreateCouponRequest $request): CouponResource
    {
        /** @var array<string, mixed> $data */
        $data = $request->validated();

        /** @var Coupon $coupon */
        $coupon = Coupon::query()->create($data);

        return new CouponResource($coupon);
    }
}
