<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Models\Order;
use App\Models\Product;

/**
 * Builds WhatsApp share deep links (wa.me) for products and orders.
 */
class ShareService
{
    /**
     * A wa.me share link carrying a pre-filled message.
     */
    public function whatsapp(string $message): string
    {
        return 'https://wa.me/?text='.rawurlencode($message);
    }

    /**
     * Share link for a product.
     */
    public function product(Product $product): string
    {
        $url = url('/products/'.$product->slug);

        return $this->whatsapp(__('bonus.share.product', ['name' => $product->name, 'url' => $url]));
    }

    /**
     * Share link for an order summary.
     */
    public function order(Order $order): string
    {
        return $this->whatsapp(__('bonus.share.order', [
            'order' => $order->order_number,
            'total' => $order->currency.' '.$order->total,
        ]));
    }
}
