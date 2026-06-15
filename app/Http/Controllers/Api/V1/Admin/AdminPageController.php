<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreatePageRequest;
use App\Http\Resources\Cms\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin CMS page management.
 */
class AdminPageController extends Controller
{
    /**
     * GET /admin/pages — list pages.
     */
    public function index(): AnonymousResourceCollection
    {
        return PageResource::collection(Page::query()->latest()->paginate(20));
    }

    /**
     * POST /admin/pages — create a page.
     */
    public function store(CreatePageRequest $request): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->validated();

        /** @var Page $page */
        $page = Page::query()->create($data);

        return (new PageResource($page))->response()->setStatusCode(201);
    }

    /**
     * DELETE /admin/pages/{page} — delete a page.
     */
    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return new JsonResponse(['message' => __('cms.page.deleted')]);
    }
}
