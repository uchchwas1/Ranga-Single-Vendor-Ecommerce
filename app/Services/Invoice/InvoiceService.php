<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\Invoice\Contracts\InvoiceRenderer;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

/**
 * Application service for generating and retrieving order invoices.
 */
class InvoiceService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly InvoiceRenderer $renderer,
    ) {
    }

    /**
     * Generate (or return the existing) invoice for an order, rendering
     * and storing the PDF on the configured disk.
     */
    public function generate(Order $order): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = $order->invoice()->firstOrCreate(
            ['order_id' => $order->id],
            [
                'invoice_number' => 'INV-'.$order->order_number,
                'issued_at' => Date::now(),
            ],
        );

        if ($invoice->pdf_path !== null && $this->disk()->exists($invoice->pdf_path)) {
            return $invoice;
        }

        $path = 'invoices/'.$invoice->invoice_number.'.pdf';
        $this->disk()->put($path, $this->renderer->render($order, $invoice));

        $invoice->forceFill([
            'pdf_path' => $path,
            'issued_at' => $invoice->issued_at ?? Date::now(),
        ])->save();

        return $invoice;
    }

    /**
     * Retrieve the stored PDF contents for an order's invoice, if present.
     */
    public function pdfContents(Invoice $invoice): ?string
    {
        if ($invoice->pdf_path === null || ! $this->disk()->exists($invoice->pdf_path)) {
            return null;
        }

        $contents = $this->disk()->get($invoice->pdf_path);

        return is_string($contents) ? $contents : null;
    }

    /**
     * The filesystem disk used for invoice storage.
     */
    private function disk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        return Storage::disk((string) config('ranga.invoices.disk', 'local'));
    }
}
