<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateBannerRequest;
use App\Http\Resources\Cms\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin banner management.
 */
class AdminBannerController extends Controller
{
    /**
     * GET /admin/banners — list banners.
     */
    public function index(): AnonymousResourceCollection
    {
        return BannerResource::collection(Banner::query()->orderBy('position')->orderBy('sort_order')->paginate(30));
    }

    /**
     * POST /admin/banners — create a banner.
     */
    public function store(CreateBannerRequest $request): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->validated();

        /** @var Banner $banner */
        $banner = Banner::query()->create($data);

        return (new BannerResource($banner))->response()->setStatusCode(201);
    }

    /**
     * DELETE /admin/banners/{banner} — delete a banner.
     */
    public function destroy(Banner $banner): JsonResponse
    {
        $banner->delete();

        return new JsonResponse(['message' => __('cms.banner.deleted')]);
    }
}
