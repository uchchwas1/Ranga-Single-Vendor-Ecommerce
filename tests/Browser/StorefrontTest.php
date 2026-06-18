<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Smoke browser tests for the public storefront shell.
 *
 * Run with: php artisan dusk
 */
class StorefrontTest extends DuskTestCase
{
    /**
     * The home page renders.
     */
    public function test_the_home_page_loads(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/')->assertPresent('body');
        });
    }

    /**
     * The offline page shows the brand name.
     */
    public function test_the_offline_page_is_branded(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/offline')->assertSee((string) config('ranga.brand.name', 'Ranga'));
        });
    }

    /**
     * The PWA manifest is reachable and well-formed.
     */
    public function test_the_manifest_is_available(): void
    {
        $this->browse(function (Browser $browser): void {
            $browser->visit('/manifest.json')->assertSee('standalone');
        });
    }
}
