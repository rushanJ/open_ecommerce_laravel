<?php

return [
    'store' => [
        'name' => env('OPEN_ECOMMERCE_LARAVEL_STORE_NAME', 'open_ecommerce_laravel'),
        'currency_code' => env('OPEN_ECOMMERCE_LARAVEL_CURRENCY_CODE', 'LKR'),
    ],
    'payments' => [
        'payhere' => [
            'enabled' => env('PAYHERE_ENABLED', false),
            'mode' => env('PAYHERE_MODE', 'sandbox'),
            'merchant_id' => env('PAYHERE_MERCHANT_ID'),
            'merchant_secret' => env('PAYHERE_MERCHANT_SECRET'),
            'sandbox_url' => 'https://sandbox.payhere.lk/pay/checkout',
            'live_url' => 'https://www.payhere.lk/pay/checkout',
        ],
    ],
    'admin' => [
        'create_pin' => env('ADMIN_CREATE_PIN'),
        'theme' => [
            'default_mode' => env('OPEN_ECOMMERCE_LARAVEL_ADMIN_THEME', 'light'),
            'brand_name' => env('OPEN_ECOMMERCE_LARAVEL_ADMIN_BRAND_NAME', 'open_ecommerce_laravel'),
            'primary_color' => env('OPEN_ECOMMERCE_LARAVEL_ADMIN_PRIMARY_COLOR', '#2563eb'),
        ],
    ],
];
