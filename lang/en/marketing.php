<?php

declare(strict_types=1);

return [

    'coupon_type' => [
        'percent' => 'Percentage',
        'fixed' => 'Fixed amount',
        'free_shipping' => 'Free shipping',
    ],

    'loyalty_type' => [
        'earn' => 'Earned',
        'redeem' => 'Redeemed',
        'expire' => 'Expired',
        'adjust' => 'Adjusted',
    ],

    'affiliate_status' => [
        'pending' => 'Pending',
        'active' => 'Active',
        'suspended' => 'Suspended',
    ],

    'conversion_status' => [
        'pending' => 'Pending',
        'approved' => 'Approved',
        'paid' => 'Paid',
        'rejected' => 'Rejected',
    ],

    'coupon' => [
        'invalid' => 'This coupon code is invalid or has expired.',
        'min_order' => 'A minimum order of :amount is required for this coupon.',
        'user_limit' => 'You have already used this coupon the maximum number of times.',
    ],

    'gift_card' => [
        'invalid' => 'This gift card is invalid or has no balance.',
    ],

    'loyalty' => [
        'insufficient_points' => 'You do not have enough points to redeem.',
        'login_required' => 'Please sign in to redeem loyalty points.',
    ],

    'bundle' => [
        'not_found' => 'The requested bundle could not be found.',
    ],

    'affiliate' => [
        'not_found' => 'The affiliate code is not recognised.',
    ],

];
