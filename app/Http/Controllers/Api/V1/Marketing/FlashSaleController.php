<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Marketing\FlashSaleResource;
use App\Services\Marketing\FlashSaleService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Public flash-sale browsing.
 */
class FlashSaleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly FlashSaleService $flashSales,
    ) {
    }

    /**
     * GET /flash-sales/active — currently live flash sales.
     */
    public function active(): AnonymousResourceCollection
    {
        return FlashSaleResource::collection($this->flashSales->active());
    }
}
