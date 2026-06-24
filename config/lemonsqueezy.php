<?php

return [
    'api_key' => env('LEMONSQUEEZY_API_KEY'),
    'store_id' => env('LEMONSQUEEZY_STORE_ID'),
    'webhook_secret' => env('LEMONSQUEEZY_WEBHOOK_SECRET'),
    'variants' => [
        'baslangic' => env('LEMONSQUEEZY_VARIANT_BASLANGIC'),
        'profesyonel' => env('LEMONSQUEEZY_VARIANT_PROFESYONEL'),
        'kurumsal' => env('LEMONSQUEEZY_VARIANT_KURUMSAL'),
    ],
];
