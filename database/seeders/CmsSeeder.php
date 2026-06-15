<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Menu;
use App\Models\Page;
use App\Support\Enums\BannerPosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;

/**
 * Seeds baseline CMS content: pages, a hero banner, a header menu and a blog post.
 */
class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['about-us' => 'About Us', 'privacy-policy' => 'Privacy Policy', 'terms' => 'Terms & Conditions'] as $slug => $title) {
            Page::query()->updateOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => "<p>{$title}</p>", 'is_published' => true],
            );
        }

        Banner::query()->updateOrCreate(
            ['title' => 'Eid Collection'],
            [
                'image' => 'banners/eid-hero.webp',
                'position' => BannerPosition::Hero,
                'sort_order' => 0,
                'is_active' => true,
            ],
        );

        /** @var Menu $menu */
        $menu = Menu::query()->updateOrCreate(['location' => 'header'], ['name' => 'Header Menu']);
        if ($menu->items()->count() === 0) {
            foreach (['Shop' => '/products', 'Flash Sales' => '/flash-sales', 'Blog' => '/blog'] as $label => $url) {
                $menu->items()->create(['label' => $label, 'url' => $url]);
            }
        }

        /** @var BlogCategory $category */
        $category = BlogCategory::query()->updateOrCreate(
            ['slug' => 'style-guide'],
            ['name' => 'Style Guide'],
        );

        BlogPost::query()->updateOrCreate(
            ['slug' => 'eid-fashion-trends-2026'],
            [
                'category_id' => $category->id,
                'title' => 'Eid Fashion Trends 2026',
                'excerpt' => 'The colours and cuts defining this season.',
                'content' => '<p>Trends...</p>',
                'published_at' => Date::now()->subDay(),
            ],
        );
    }
}
