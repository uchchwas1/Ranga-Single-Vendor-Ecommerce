<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e5e7eb; }
        .right { text-align: right; }
        .totals td { border: none; }
    </style>
</head>
<body>
    <h1>{{ config('ranga.brand.name', 'Ranga') }}</h1>
    <div class="muted">{{ __('commerce.invoice.title') }}: {{ $invoice->invoice_number }}</div>
    <div class="muted">{{ __('commerce.invoice.order') }}: {{ $order->order_number }}</div>
    <div class="muted">{{ optional($invoice->issued_at)->format('d/m/Y') }}</div>

    <table>
        <thead>
            <tr>
                <th>{{ __('commerce.invoice.item') }}</th>
                <th class="right">{{ __('commerce.invoice.qty') }}</th>
                <th class="right">{{ __('commerce.invoice.unit_price') }}</th>
                <th class="right">{{ __('commerce.invoice.subtotal') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">{{ $item->unit_price }}</td>
                    <td class="right">{{ $item->subtotal }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>{{ __('commerce.invoice.subtotal') }}</td><td class="right">{{ $order->subtotal }}</td></tr>
        <tr><td>{{ __('commerce.invoice.shipping') }}</td><td class="right">{{ $order->shipping_amount }}</td></tr>
        <tr><td>{{ __('commerce.invoice.tax') }}</td><td class="right">{{ $order->tax_amount }}</td></tr>
        <tr><td><strong>{{ __('commerce.invoice.total') }}</strong></td><td class="right"><strong>{{ $order->currency }} {{ $order->total }}</strong></td></tr>
    </table>
</body>
</html>
