<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, SparkPost and others. This file provides a sane default
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'detran' => [
        'url' => env('DETRAN_API_URL', 'https://api.detran.mock/v1'),
        'token' => env('DETRAN_API_TOKEN', 'demo-token'),
        'timeout' => env('DETRAN_API_TIMEOUT', 5),
        'retry_times' => env('DETRAN_API_RETRY_TIMES', 2),
        'retry_sleep' => env('DETRAN_API_RETRY_SLEEP', 100),
    ],

    'mercadopago' => [
        'access_token' => env('MERCADOPAGO_ACCESS_TOKEN'),
        'public_key' => env('MERCADOPAGO_PUBLIC_KEY'),
        'webhook_secret' => env('MERCADOPAGO_WEBHOOK_SECRET'),
        'timeout' => env('MERCADOPAGO_TIMEOUT', 30),
        'sandbox' => env('MERCADOPAGO_SANDBOX', true),
    ],

];
