<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Product;

/**
 * Builds Schema.org JSON-LD structured data (blueprint 2.13).
 */
class SchemaService
{
    /**
     * Product structured data, including offers and availability.
     *
     * @return array<string, mixed>
     */
    public function product(Product $product): array
    {
        $price = $product->priceFrom();
        $inStock = $product->variants->contains(fn ($v): bool => $v->is_active && $v->stock > 0);

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'sku' => $product->sku,
            'description' => strip_tags((string) $product->short_description),
            'url' => url('/products/'.$product->slug),
        ];

        if ($product->brand !== null) {
            $schema['brand'] = ['@type' => 'Brand', 'name' => $product->brand->name];
        }

        if ($price !== null) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'priceCurrency' => (string) config('ranga.defaults.currency', 'BDT'),
                'price' => number_format($price, 2, '.', ''),
                'availability' => $inStock
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url' => url('/products/'.$product->slug),
            ];
        }

        return $schema;
    }

    /**
     * A breadcrumb trail.
     *
     * @param  list<array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumb(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_map(
                static fn (array $item, int $i): array => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ],
                $items,
                array_keys($items),
            )),
        ];
    }

    /**
     * Organization structured data.
     *
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => (string) config('ranga.seo.organization_name', 'Ranga'),
            'url' => url('/'),
            'logo' => url((string) config('ranga.brand.logo', '/images/logo.svg')),
        ];
    }

    /**
     * BlogPosting structured data.
     *
     * @return array<string, mixed>
     */
    public function blogPosting(BlogPost $post): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post->title,
            'datePublished' => $post->published_at?->toAtomString(),
            'author' => ['@type' => 'Person', 'name' => $post->author?->name ?? 'Editorial'],
            'url' => url('/blog/'.$post->slug),
        ];
    }

    /**
     * FAQPage structured data from question/answer pairs.
     *
     * @param  list<array{q: string, a: string}>  $faqs
     * @return array<string, mixed>
     */
    public function faqPage(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_values(array_map(
                static fn (array $faq): array => [
                    '@type' => 'Question',
                    'name' => $faq['q'] ?? '',
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a'] ?? ''],
                ],
                $faqs,
            )),
        ];
    }
}
