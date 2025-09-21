<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Currency Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration sets the default currency settings for the Philippines.
    | Used for formatting monetary values throughout the application.
    |
    */

    'default' => [
        'code' => 'PHP',
        'symbol' => '₱',
        'decimal_places' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
        'symbol_position' => 'before', // 'before' or 'after'
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for PHP number formatting specific to the Philippines.
    |
    */

    'locale' => 'en_PH',
    'number_format' => [
        'decimals' => 2,
        'decimal_separator' => '.',
        'thousands_separator' => ',',
    ],
];
