<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateFlashSaleRequest;
use App\Http\Resources\Marketing\FlashSaleResource;
use App\Models\FlashSale;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Admin flash-sale management.
 */
class AdminFlashSaleController extends Controller
{
    /**
     * POST /admin/flash-sales — create a flash sale with items.
     */
    public function store(CreateFlashSaleRequest $request): JsonResponse
    {
        /** @var array{name: string, starts_at: string, ends_at: string, is_active?: bool, items: list<array{product_id: string, variant_id?: string|null, sale_price: int|float, quantity_limit?: int|null}>} $data */
        $data = $request->validated();

        $sale = DB::transaction(function () use ($data): FlashSale {
            /** @var FlashSale $sale */
            $sale = FlashSale::query()->create([
                'name' => $data['name'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            foreach ($data['items'] as $item) {
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'sale_price' => $item['sale_price'],
                    'quantity_limit' => $item['quantity_limit'] ?? null,
                ]);
            }

            return $sale;
        });

        return (new FlashSaleResource($sale->load('items.product')))->response()->setStatusCode(201);
    }
}
