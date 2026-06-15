<?php

declare(strict_types=1);

return [

    'coupon_type' => [
        'percent' => 'শতাংশ',
        'fixed' => 'নির্দিষ্ট পরিমাণ',
        'free_shipping' => 'ফ্রি শিপিং',
    ],

    'loyalty_type' => [
        'earn' => 'অর্জিত',
        'redeem' => 'ব্যবহৃত',
        'expire' => 'মেয়াদোত্তীর্ণ',
        'adjust' => 'সমন্বিত',
    ],

    'affiliate_status' => [
        'pending' => 'অপেক্ষমাণ',
        'active' => 'সক্রিয়',
        'suspended' => 'স্থগিত',
    ],

    'conversion_status' => [
        'pending' => 'অপেক্ষমাণ',
        'approved' => 'অনুমোদিত',
        'paid' => 'পরিশোধিত',
        'rejected' => 'প্রত্যাখ্যাত',
    ],

    'coupon' => [
        'invalid' => 'এই কুপন কোডটি অবৈধ বা মেয়াদোত্তীর্ণ।',
        'min_order' => 'এই কুপনের জন্য ন্যূনতম :amount অর্ডার প্রয়োজন।',
        'user_limit' => 'আপনি এই কুপনটি সর্বোচ্চবার ব্যবহার করেছেন।',
    ],

    'gift_card' => [
        'invalid' => 'এই গিফট কার্ডটি অবৈধ বা কোনো ব্যালেন্স নেই।',
    ],

    'loyalty' => [
        'insufficient_points' => 'রিডিম করার জন্য আপনার পর্যাপ্ত পয়েন্ট নেই।',
        'login_required' => 'লয়্যালটি পয়েন্ট রিডিম করতে অনুগ্রহ করে সাইন ইন করুন।',
    ],

    'bundle' => [
        'not_found' => 'অনুরোধ করা বান্ডেলটি খুঁজে পাওয়া যায়নি।',
    ],

    'affiliate' => [
        'not_found' => 'অ্যাফিলিয়েট কোডটি স্বীকৃত নয়।',
    ],

];
