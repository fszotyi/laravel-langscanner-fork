<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Translation methods
    |--------------------------------------------------------------------------
    |
    | Which methods to look for when scanning translations.
    |
    */
    'translation_methods' => [
        '__',
        'trans',
        'trans_choice',
        '@lang',
        'Lang::get',
        'Lang::choice',
        'Lang::trans',
        'Lang::transChoice',
        '@choice'
    ],

    /*
    |--------------------------------------------------------------------------
    | Scan paths
    |--------------------------------------------------------------------------
    |
    | Which directories to scan for missing translations.
    |
    */
    'paths' => [app_path(), resource_path()],

    /*
    |--------------------------------------------------------------------------
    | Scan excluded paths
    |--------------------------------------------------------------------------
    |
    | Which directories to exclude when scanning for missing translations.
    |
    */
    'excluded_paths' => [],

    /*
    |--------------------------------------------------------------------------
    | Excluded languages
    |--------------------------------------------------------------------------
    |
    | Which languages to exclude when scanning for missing translations.
    |
    */
    'excluded_languages' => ['en'],

    /*
    |--------------------------------------------------------------------------
    | Languages path
    |--------------------------------------------------------------------------
    |
    | Where are the language .json files located
    |
    */
    'languages_path' => resource_path('lang'),
];
