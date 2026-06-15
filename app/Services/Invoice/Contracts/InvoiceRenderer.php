<?php

declare(strict_types=1);

namespace App\Services\Invoice\Contracts;

use App\Models\Invoice;
use App\Models\Order;

/**
 * Renders an invoice to PDF bytes. Abstracted so the PDF engine can be
 * swapped (and stubbed in tests) without touching the service layer.
 */
interface InvoiceRenderer
{
    /**
     * Render the given invoice/order to raw PDF bytes.
     */
    public function render(Order $order, Invoice $invoice): string;
}
