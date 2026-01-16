<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'thawani' => [
        'base_url' => env('THAWANI_TEST_CHECKOUT_URL', env('THAWANI_BASE_URL', env('THAWANI_MODE', 'test') === 'live' ? 'https://checkout.thawani.om' : 'https://uatcheckout.thawani.om')),
        'secret_key' => env('THAWANI_TEST_SECRET_KEY', env('THAWANI_SECRET_KEY')),
        'publishable_key' => env('THAWANI_TEST_PUBLIC_KEY', env('THAWANI_PUBLISHABLE_KEY', env('THAWANI_PUBLISH_KEY'))),
        'pay_url' => env('THAWANI_TEST_PAY_URL', env('THAWANI_MODE', 'test') === 'live' ? 'https://checkout.thawani.om/pay' : 'https://uatcheckout.thawani.om/pay'),
        'mode' => env('THAWANI_MODE', env('THAWANI_MOD', 'test')),
    ],

];
