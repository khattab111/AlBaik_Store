<?php

return [
    'default' => env('APP_LOCALE', 'ar'),

    'fallback' => env('APP_FALLBACK_LOCALE', 'en'),

    'supported' => [
        'ar' => [
            'name' => 'Arabic',
            'native' => 'العربية',
            'direction' => 'rtl',
            'regional' => 'ar',
        ],
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'direction' => 'ltr',
            'regional' => 'en',
        ],
    ],
];
