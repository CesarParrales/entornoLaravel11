<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Paths
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource
    | sharing or "CORS". This determines what cross-origin operations
    | can be executed in web browsers.
    |
    */

    'paths' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Matches the request method. `['*']` allows all methods.
    |
    */

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Matches the request origin. `['*']` allows all origins. Wildcards can
    | be used as `*.example.com` or `example.*`.
    |
    */

    'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '')),

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | Matches the request origin with `Preg::match`. Wildcards can be used
    | as `*.example.com` or `example.*`.
    |
    */

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Matches the request `Access-Control-Request-Headers` header. `['*']`
    | allows all headers.
    |
    */

    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Sets the `Access-Control-Expose-Headers` header.
    |
    */

    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Sets the `Access-Control-Max-Age` header.
    |
    */

    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Sets the `Access-Control-Allow-Credentials` header.
    |
    */

    'supports_credentials' => false,

];