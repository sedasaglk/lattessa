<?php

return [
    'api_key'        => env('LEMON_API_KEY', ''),
    'store_id'       => env('LEMON_STORE_ID', ''),
    'webhook_secret' => env('LEMON_WEBHOOK_SECRET', ''),

    // LemonSqueezy Variant ID → paket slug eşleştirmesi
    'variants' => [
        'baslangic'   => env('LEMON_VARIANT_BASLANGIC', ''),
        'profesyonel' => env('LEMON_VARIANT_PROFESYONEL', ''),
        'kurumsal'    => env('LEMON_VARIANT_KURUMSAL', ''),
        'plus'        => env('LEMON_VARIANT_PLUS', ''),
        'premium'     => env('LEMON_VARIANT_PREMIUM', ''),
    ],
];
