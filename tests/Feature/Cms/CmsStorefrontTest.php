<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsStorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_published_blog_posts_and_records_a_view(): void
    {
        $post = BlogPost::factory()->create(['slug' => 'eid-trends']);
        BlogPost::factory()->draft()->create();

        $this->getJson('/api/v1/blog')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'eid-trends');

        $this->getJson('/api/v1/blog/eid-trends')->assertOk()->assertJsonPath('data.slug', 'eid-trends');
        $this->assertSame(1, $post->fresh()?->view_count);
    }

    public function test_it_returns_live_banners_for_a_position(): void
    {
        Banner::factory()->create(['position' => 'hero']);

        $this->getJson('/api/v1/banners?position=hero')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_it_serves_a_published_page(): void
    {
        Page::factory()->create(['slug' => 'about-us']);

        $this->getJson('/api/v1/pages/about-us')
            ->assertOk()
            ->assertJsonPath('data.slug', 'about-us');
    }

    public function test_the_sitemap_includes_active_products(): void
    {
        $product = Product::factory()->create(['slug' => 'crimson-saree']);
        ProductVariant::factory()->for($product)->create();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertSame('application/xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('crimson-saree', $response->getContent() ?: '');
    }

    public function test_robots_txt_disallows_private_areas(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $body = $response->getContent() ?: '';
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Sitemap:', $body);
    }
}
