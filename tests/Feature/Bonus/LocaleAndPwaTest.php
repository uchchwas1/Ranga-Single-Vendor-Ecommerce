<?php

declare(strict_types=1);

namespace Tests\Feature\Bonus;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleAndPwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_supported_locales(): void
    {
        $this->getJson('/api/v1/i18n/locales')
            ->assertOk()
            ->assertJsonPath('default', 'bn')
            ->assertJsonPath('supported', ['bn', 'en']);
    }

    public function test_the_locale_header_switches_the_active_locale(): void
    {
        $this->withHeaders(['X-Locale' => 'en'])
            ->getJson('/api/v1/i18n/locales')
            ->assertOk()
            ->assertJsonPath('current', 'en');
    }

    public function test_the_pwa_manifest_is_served(): void
    {
        $this->get('/manifest.json')
            ->assertOk()
            ->assertJsonPath('display', 'standalone')
            ->assertJsonPath('name', config('ranga.brand.name'));
    }

    public function test_the_service_worker_and_offline_page_are_served(): void
    {
        $this->get('/sw.js')->assertOk()->assertHeader('Content-Type', 'application/javascript');
        $this->get('/offline')->assertOk();
    }
}
