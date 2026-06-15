<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateMenuRequest;
use App\Http\Resources\Cms\MenuResource;
use App\Models\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Admin navigation-menu management.
 */
class AdminMenuController extends Controller
{
    /**
     * POST /admin/menus — create a menu with top-level items.
     */
    public function store(CreateMenuRequest $request): JsonResponse
    {
        /** @var array{name: string, location: string, items?: list<array{label: string, url?: string|null, target?: string, sort_order?: int}>} $data */
        $data = $request->validated();

        $menu = DB::transaction(function () use ($data): Menu {
            /** @var Menu $menu */
            $menu = Menu::query()->create(['name' => $data['name'], 'location' => $data['location']]);

            foreach ($data['items'] ?? [] as $item) {
                $menu->items()->create([
                    'label' => $item['label'],
                    'url' => $item['url'] ?? null,
                    'target' => $item['target'] ?? '_self',
                    'sort_order' => $item['sort_order'] ?? 0,
                ]);
            }

            return $menu;
        });

        return (new MenuResource($menu->load('items.children')))->response()->setStatusCode(201);
    }

    /**
     * GET /admin/menus/{menu} — show a menu with its items.
     */
    public function show(Menu $menu): MenuResource
    {
        return new MenuResource($menu->load('items.children'));
    }
}
