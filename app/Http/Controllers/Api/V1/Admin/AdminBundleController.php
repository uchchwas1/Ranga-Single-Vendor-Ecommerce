<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateBundleRequest;
use App\Http\Resources\Marketing\BundleResource;
use App\Models\Bundle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Admin product-bundle management.
 */
class AdminBundleController extends Controller
{
    /**
     * POST /admin/bundles — create a bundle with items.
     */
    public function store(CreateBundleRequest $request): JsonResponse
    {
        /** @var array{name: string, slug: string, description?: string|null, price?: int|float|null, discount_percent?: int|float, is_active?: bool, items: list<array{product_id: string, variant_id?: string|null, quantity?: int}>} $data */
        $data = $request->validated();

        $bundle = DB::transaction(function () use ($data): Bundle {
            /** @var Bundle $bundle */
            $bundle = Bundle::query()->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'price' => $data['price'] ?? null,
                'discount_percent' => $data['discount_percent'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            foreach ($data['items'] as $item) {
                $bundle->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                ]);
            }

            return $bundle;
        });

        return (new BundleResource($bundle->load('items.product')))->response()->setStatusCode(201);
    }
}
