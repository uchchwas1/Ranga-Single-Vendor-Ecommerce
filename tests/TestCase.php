<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Base test case for the application.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Run artisan commands directly (without console output mocking),
     * which is portable across constrained PHP runtimes.
     *
     * @var bool
     */
    public $mockConsoleOutput = false;

    /**
     * Bootstrap the test environment.
     *
     * Stub out Vite so view-rendering tests never depend on a compiled
     * asset manifest (public/build/manifest.json).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }
}
