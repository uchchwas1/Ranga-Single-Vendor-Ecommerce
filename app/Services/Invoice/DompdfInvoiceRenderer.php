<?php

declare(strict_types=1);

namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\Invoice\Contracts\InvoiceRenderer;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Renders invoices to PDF using dompdf via a Blade template.
 */
class DompdfInvoiceRenderer implements InvoiceRenderer
{
    /**
     * Render the given invoice/order to raw PDF bytes.
     */
    public function render(Order $order, Invoice $invoice): string
    {
        return Pdf::loadView('invoices.invoice', [
            'order' => $order->loadMissing(['items', 'addresses']),
            'invoice' => $invoice,
        ])->output();
    }
}
