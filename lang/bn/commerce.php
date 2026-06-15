<?php

declare(strict_types=1);

return [

    'order_status' => [
        'pending' => 'অপেক্ষমাণ',
        'confirmed' => 'নিশ্চিত',
        'processing' => 'প্রক্রিয়াধীন',
        'shipped' => 'পাঠানো হয়েছে',
        'delivered' => 'ডেলিভারি সম্পন্ন',
        'completed' => 'সম্পন্ন',
        'cancelled' => 'বাতিল',
        'refunded' => 'ফেরত',
    ],

    'payment_status' => [
        'pending' => 'অপেক্ষমাণ',
        'paid' => 'পরিশোধিত',
        'failed' => 'ব্যর্থ',
        'refunded' => 'ফেরত',
    ],

    'shipping_status' => [
        'pending' => 'অপেক্ষমাণ',
        'processing' => 'প্রক্রিয়াধীন',
        'shipped' => 'পাঠানো হয়েছে',
        'delivered' => 'ডেলিভারি সম্পন্ন',
    ],

    'gateway' => [
        'sslcommerz' => 'এসএসএলকমার্জ',
        'bkash' => 'বিকাশ',
        'nagad' => 'নগদ',
        'stripe' => 'স্ট্রাইপ',
        'paypal' => 'পেপ্যাল',
        'cod' => 'ক্যাশ অন ডেলিভারি',
        'gift_card' => 'গিফট কার্ড',
        'loyalty' => 'লয়্যালটি পয়েন্ট',
    ],

    'cart' => [
        'product_unavailable' => 'এই পণ্যটি আর পাওয়া যাচ্ছে না।',
        'variant_unavailable' => 'নির্বাচিত ভ্যারিয়েন্টটি পাওয়া যাচ্ছে না।',
        'insufficient_stock' => 'মাত্র :available টি স্টকে আছে।',
        'item_not_found' => 'কার্টের আইটেমটি খুঁজে পাওয়া যায়নি।',
        'saved_not_found' => 'সংরক্ষিত আইটেমটি খুঁজে পাওয়া যায়নি।',
        'login_required' => 'এই সুবিধা ব্যবহার করতে অনুগ্রহ করে সাইন ইন করুন।',
    ],

    'wishlist' => [
        'added' => 'আপনার উইশলিস্টে যোগ করা হয়েছে।',
        'removed' => 'আপনার উইশলিস্ট থেকে সরানো হয়েছে।',
    ],

    'checkout' => [
        'empty_cart' => 'আপনার কার্ট খালি।',
        'guest_email_required' => 'গেস্ট চেকআউটের জন্য একটি ইমেল ঠিকানা প্রয়োজন।',
        'gateway_unsupported' => 'নির্বাচিত পেমেন্ট পদ্ধতি উপলব্ধ নয়।',
        'shipping_unavailable' => 'নির্বাচিত শিপিং পদ্ধতি উপলব্ধ নয়।',
        'payment_not_found' => 'উল্লিখিত পেমেন্ট খুঁজে পাওয়া যায়নি।',
        'order_not_found' => 'অনুরোধ করা অর্ডারটি খুঁজে পাওয়া যায়নি।',
        'unknown_product' => 'অজানা পণ্য',
    ],

    'payment' => [
        'cod_placed' => 'আপনার অর্ডার সম্পন্ন হয়েছে। ডেলিভারিতে পেমেন্ট করুন।',
        'init_failed' => 'পেমেন্ট শুরু করা যায়নি। আবার চেষ্টা করুন।',
    ],

    'shipment_status' => [
        'pending' => 'অপেক্ষমাণ',
        'processing' => 'প্রক্রিয়াধীন',
        'shipped' => 'পাঠানো হয়েছে',
        'in_transit' => 'পরিবহনে',
        'delivered' => 'ডেলিভারি সম্পন্ন',
        'returned' => 'ফেরত',
    ],

    'return_status' => [
        'pending' => 'অপেক্ষমাণ',
        'approved' => 'অনুমোদিত',
        'rejected' => 'প্রত্যাখ্যাত',
        'completed' => 'সম্পন্ন',
    ],

    'refund_status' => [
        'pending' => 'অপেক্ষমাণ',
        'processing' => 'প্রক্রিয়াধীন',
        'completed' => 'সম্পন্ন',
        'failed' => 'ব্যর্থ',
    ],

    'refund_method' => [
        'original_payment' => 'মূল পেমেন্ট মাধ্যম',
        'store_credit' => 'স্টোর ক্রেডিট',
        'manual' => 'ম্যানুয়াল',
    ],

    'order' => [
        'not_cancellable' => 'এই অর্ডারটি আর বাতিল করা যাবে না।',
    ],

    'shipment' => [
        'dispatched' => 'আপনার অর্ডার পাঠানো হয়েছে।',
        'delivered' => 'আপনার অর্ডার ডেলিভারি সম্পন্ন হয়েছে।',
    ],

    'return' => [
        'not_returnable' => 'এই অর্ডারটি ফেরতের জন্য যোগ্য নয়।',
        'not_pending' => 'এই ফেরত অনুরোধটি ইতিমধ্যে প্রক্রিয়া করা হয়েছে।',
    ],

    'invoice' => [
        'title' => 'ইনভয়েস',
        'order' => 'অর্ডার',
        'item' => 'পণ্য',
        'qty' => 'পরিমাণ',
        'unit_price' => 'একক মূল্য',
        'subtotal' => 'সাবটোটাল',
        'shipping' => 'শিপিং',
        'tax' => 'কর',
        'total' => 'মোট',
        'unavailable' => 'ইনভয়েস এখনও উপলব্ধ নয়।',
    ],

];
