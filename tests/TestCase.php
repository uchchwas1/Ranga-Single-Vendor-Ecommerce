<?php

declare(strict_types=1);

namespace Tests;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\Invoice\Contracts\InvoiceRenderer;
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

        // Stub the PDF engine so the suite never depends on dompdf rendering.
        $this->app->bind(InvoiceRenderer::class, fn (): InvoiceRenderer => new class implements InvoiceRenderer {
            public function render(Order $order, Invoice $invoice): string
            {
                return '%PDF-1.4 stub';
            }
        });
    }
}
