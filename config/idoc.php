<?php

return [
    /*
    |--------------------------------------------------------------------------
    | iDoc Domain
    |--------------------------------------------------------------------------
    | Set to null to use the same domain as the application.
    */
    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | iDoc Path
    |--------------------------------------------------------------------------
    | The URI path where the documentation will be accessible.
    */
    'path' => 'api-docs',

    /*
    |--------------------------------------------------------------------------
    | iDoc Route Middleware
    |--------------------------------------------------------------------------
    */
    'middleware' => [
        'web',
    ],

    /*
    |--------------------------------------------------------------------------
    | iDoc Logo
    |--------------------------------------------------------------------------
    */
    'logo' => null,

    'color' => '#1a56db',

    /*
    |--------------------------------------------------------------------------
    | iDoc Principal Information
    |--------------------------------------------------------------------------
    */
    'title'       => 'ZKTeco Biometric Integration API',
    'description' => 'API documentation for the ZKTeco Biometric Integration system. Manage organizations, devices, and attendance data.',
    'version'     => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Routes to Document
    |--------------------------------------------------------------------------
    | Routes matching these patterns will be included in documentation.
    */
    'routes' => [
        [
            'match' => [
                'domains' => ['*'],
                'prefixes' => ['api/*'],
                'versions' => ['v1'],
            ],
            'include' => [],
            'exclude' => [],
            'apply' => [
                'headers' => [
                    [
                        'name'    => 'Accept',
                        'value'   => 'application/json',
                        'when'    => [],
                        'except'  => [],
                    ],
                ],
                'response_calls' => [
                    'methods'       => ['GET'],
                    'config'        => [],
                    'cookies'       => [],
                    'query_params'  => [],
                    'body_params'   => [],
                ],
            ],
        ],
    ],
];
