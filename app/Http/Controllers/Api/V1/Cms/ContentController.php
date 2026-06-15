<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Cms;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\BannerResource;
use App\Http\Resources\Cms\MenuResource;
use App\Http\Resources\Cms\PageResource;
use App\Http\Resources\Cms\PopupResource;
use App\Services\Cms\ContentService;
use App\Support\Enums\BannerPosition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public storefront CMS content: pages, banners, menus, popups.
 */
class ContentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ContentService $content,
    ) {
    }

    /**
     * GET /pages/{slug} — a published page.
     */
    public function page(string $slug): PageResource
    {
        $page = $this->content->publishedPage($slug);

        if ($page === null) {
            throw new NotFoundHttpException(__('cms.page.not_found'));
        }

        return new PageResource($page);
    }

    /**
     * GET /banners — live banners for a position (default hero).
     */
    public function banners(Request $request): AnonymousResourceCollection
    {
        $position = BannerPosition::tryFrom((string) $request->query('position', 'hero')) ?? BannerPosition::Hero;

        return BannerResource::collection($this->content->banners($position));
    }

    /**
     * GET /menus/{location} — a navigation menu.
     */
    public function menu(string $location): MenuResource
    {
        $menu = $this->content->menu($location);

        if ($menu === null) {
            throw new NotFoundHttpException(__('cms.menu.not_found'));
        }

        return new MenuResource($menu);
    }

    /**
     * GET /popups — active storefront popups.
     */
    public function popups(): AnonymousResourceCollection
    {
        return PopupResource::collection($this->content->activePopups());
    }
}
