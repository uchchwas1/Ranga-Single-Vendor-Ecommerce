<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    use RefreshDatabase;

    private SettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(SettingsService::class);
    }

    public function test_get_returns_default_when_setting_is_missing(): void
    {
        $this->assertSame('fallback', $this->service->get('general', 'missing', 'fallback'));
    }

    public function test_set_then_get_round_trips_through_the_cache(): void
    {
        $this->service->set('general', 'brand_name', 'Ranga', true);

        $this->assertSame('Ranga', $this->service->get('general', 'brand_name'));

        // Update busts the cache entry
        $this->service->set('general', 'brand_name', 'Rebrand');
        $this->assertSame('Rebrand', $this->service->get('general', 'brand_name'));
    }

    public function test_public_settings_excludes_private_groups(): void
    {
        Setting::factory()->public()->create(['group' => 'general', 'key' => 'brand_name', 'value' => 'Ranga']);
        Setting::factory()->create(['group' => 'security', 'key' => 'secret', 'value' => 'x']);

        $public = $this->service->publicSettings();

        $this->assertArrayHasKey('general', $public);
        $this->assertArrayNotHasKey('security', $public);
    }
}
