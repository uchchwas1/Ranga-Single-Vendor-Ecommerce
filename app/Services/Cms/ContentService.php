<?php

declare(strict_types=1);

namespace App\Services\Cms;

use App\Models\Banner;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Popup;
use App\Services\Support\CacheService;
use App\Support\Enums\BannerPosition;
use Illuminate\Database\Eloquent\Collection;

/**
 * Read-side service for storefront CMS content.
 */
class ContentService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly CacheService $cache,
    ) {
    }

    /**
     * Find a published page by slug.
     */
    public function publishedPage(string $slug): ?Page
    {
        return Page::query()->published()->where('slug', $slug)->first();
    }

    /**
     * Live banners for a given position, ordered for display.
     *
     * Cached under the homepage tag; invalidated by the banner observer.
     *
     * @return Collection<int, Banner>
     */
    public function banners(BannerPosition $position): Collection
    {
        return $this->cache->remember(
            ['homepage'],
            'banners:'.$position->value,
            (int) config('ranga.cache.homepage_ttl', 1800),
            static fn (): Collection => Banner::query()->live()->where('position', $position->value)->get(),
        );
    }

    /**
     * A navigation menu (with nested items) by location.
     */
    public function menu(string $location): ?Menu
    {
        return Menu::query()
            ->where('location', $location)
            ->with(['items.children'])
            ->first();
    }

    /**
     * Active storefront popups.
     *
     * @return Collection<int, Popup>
     */
    public function activePopups(): Collection
    {
        return Popup::query()->active()->get();
    }
}
