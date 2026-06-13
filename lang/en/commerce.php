<?php

declare(strict_types=1);

return [

    'order_status' => [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'refunded' => 'Refunded',
    ],

    'payment_status' => [
        'pending' => 'Pending',
        'paid' => 'Paid',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ],

    'shipping_status' => [
        'pending' => 'Pending',
        'processing' => 'Processing',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
    ],

    'gateway' => [
        'sslcommerz' => 'SSLCommerz',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'stripe' => 'Stripe',
        'paypal' => 'PayPal',
        'cod' => 'Cash on Delivery',
        'gift_card' => 'Gift Card',
        'loyalty' => 'Loyalty Points',
    ],

    'cart' => [
        'product_unavailable' => 'This product is no longer available.',
        'variant_unavailable' => 'The selected variant is unavailable.',
        'insufficient_stock' => 'Only :available item(s) are in stock.',
        'item_not_found' => 'That cart item could not be found.',
        'saved_not_found' => 'That saved item could not be found.',
        'login_required' => 'Please sign in to use this feature.',
    ],

    'wishlist' => [
        'added' => 'Added to your wishlist.',
        'removed' => 'Removed from your wishlist.',
    ],

    'checkout' => [
        'empty_cart' => 'Your cart is empty.',
        'guest_email_required' => 'An email address is required for guest checkout.',
        'gateway_unsupported' => 'The selected payment method is not available.',
        'shipping_unavailable' => 'The selected shipping method is unavailable.',
        'payment_not_found' => 'The referenced payment could not be found.',
        'order_not_found' => 'The requested order could not be found.',
        'unknown_product' => 'Unknown product',
    ],

    'payment' => [
        'cod_placed' => 'Your order has been placed. Pay on delivery.',
        'init_failed' => 'We could not start the payment. Please try again.',
    ],

];
