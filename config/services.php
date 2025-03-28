<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe'   => [
        'model'  => App\User::class,
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID', '339418636721296'),
        'secret'    => env('FACEBOOK_SECRET', 'ff836a9cba72cd8fd58cbf011a739ae5'),
    ],
    'google'   => [
        'client_id' => env('GOOGLE_CLIENT_ID', '787559627869-cun3uirfqe1jfnll87ard849190fo2l3.apps.googleusercontent.com'),
        'secret'    => env('GOOGLE_SECRET', 'sf4anpcBADzlGUYtn9EyLC39'),
    ]

];
