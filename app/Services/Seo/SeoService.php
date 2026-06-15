<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\Brand;
use App\Models\Category;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Product;
use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;

/**
 * SEO metadata management plus sitemap / robots generation.
 */
class SeoService
{
    /**
     * Get the SEO metadata for a model, if any.
     */
    public function forModel(Model $model): ?SeoMeta
    {
        return SeoMeta::query()
            ->where('model_type', $model->getMorphClass())
            ->where('model_id', $model->getKey())
            ->first();
    }

    /**
     * Create or update the SEO metadata for a model.
     *
     * @param  array<string, mixed>  $data
     */
    public function upsertFor(Model $model, array $data): SeoMeta
    {
        /** @var SeoMeta $meta */
        $meta = SeoMeta::query()->updateOrCreate(
            ['model_type' => $model->getMorphClass(), 'model_id' => $model->getKey()],
            $data,
        );

        return $meta;
    }

    /**
     * Build the list of sitemap URLs for all indexable content.
     *
     * @return list<array{loc: string, lastmod: string|null}>
     */
    public function sitemapUrls(): array
    {
        $urls = [];

        Product::query()->active()->get(['slug', 'updated_at'])
            ->each(function (Product $p) use (&$urls): void {
                $urls[] = ['loc' => url('/products/'.$p->slug), 'lastmod' => $p->updated_at?->toAtomString()];
            });

        Category::query()->active()->get(['slug', 'updated_at'])
            ->each(function (Category $c) use (&$urls): void {
                $urls[] = ['loc' => url('/categories/'.$c->slug), 'lastmod' => $c->updated_at?->toAtomString()];
            });

        Brand::query()->active()->get(['slug', 'updated_at'])
            ->each(function (Brand $b) use (&$urls): void {
                $urls[] = ['loc' => url('/brands/'.$b->slug), 'lastmod' => $b->updated_at?->toAtomString()];
            });

        BlogPost::query()->published()->get(['slug', 'updated_at'])
            ->each(function (BlogPost $post) use (&$urls): void {
                $urls[] = ['loc' => url('/blog/'.$post->slug), 'lastmod' => $post->updated_at?->toAtomString()];
            });

        Page::query()->published()->get(['slug', 'updated_at'])
            ->each(function (Page $page) use (&$urls): void {
                $urls[] = ['loc' => url('/'.$page->slug), 'lastmod' => $page->updated_at?->toAtomString()];
            });

        return $urls;
    }

    /**
     * Render the sitemap XML document.
     */
    public function sitemapXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($this->sitemapUrls() as $url) {
            $xml .= '  <url><loc>'.htmlspecialchars($url['loc'], ENT_XML1).'</loc>';

            if ($url['lastmod'] !== null) {
                $xml .= '<lastmod>'.$url['lastmod'].'</lastmod>';
            }

            $xml .= '</url>'."\n";
        }

        return $xml.'</urlset>';
    }

    /**
     * Render the robots.txt body (private areas disallowed).
     */
    public function robotsTxt(): string
    {
        $lines = [
            'User-agent: *',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /account',
            'Disallow: /admin',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return implode("\n", $lines)."\n";
    }
}
