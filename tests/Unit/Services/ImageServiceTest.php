<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Media\ImageService;
use Tests\TestCase;

class ImageServiceTest extends TestCase
{
    public function test_it_returns_null_for_an_empty_path(): void
    {
        $this->assertNull((new ImageService())->url(null));
    }

    public function test_it_builds_an_unsigned_url_without_a_key(): void
    {
        config()->set('ranga.images.imgproxy_url', 'https://img.test');
        config()->set('ranga.images.imgproxy_key', '');
        config()->set('ranga.images.imgproxy_salt', '');
        config()->set('ranga.images.source_base', 'https://cdn.test');

        $url = (new ImageService())->url('products/a.jpg', 300, 300);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://img.test/insecure/rs:fit:300:300/f:webp/', (string) $url);
    }

    public function test_it_signs_the_url_when_a_key_is_configured(): void
    {
        config()->set('ranga.images.imgproxy_url', 'https://img.test');
        config()->set('ranga.images.imgproxy_key', bin2hex('secret-key'));
        config()->set('ranga.images.imgproxy_salt', bin2hex('secret-salt'));
        config()->set('ranga.images.source_base', 'https://cdn.test');

        $url = (string) (new ImageService())->url('products/a.jpg', 200, 200);

        $this->assertStringStartsWith('https://img.test/', $url);
        $this->assertStringNotContainsString('/insecure/', $url);
    }
}
