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
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.eu.mailgun.net'),
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

    // 'webhook' => [
    //     'url' => env('WEBHOOK_URL'),
    // ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'scopes' => [
            'https://www.googleapis.com/auth/calendar',
            'https://www.googleapis.com/auth/calendar.events',
        ],
        'google_api_key' => env('GOOGLE_API_KEY'),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'key' => env('STRIPE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),

        'plans' => [
            'test' => [
                'freelancer' => [
                    'product_id' => 'prod_QuFyGzwZRxDsqV', 
                    'price_id' => 'price_1Q67XKEEh64CES4EkbdqPmEc',
                ],
                'freelancer_pro' => [
                    'product_id' => 'prod_ProPlanID_Test', 
                    'price_id' => 'price_ProPlanPriceID_Test',
                ],
                'extra_clients' => [
                    'product_id' => 'prod_TestExtraClientsID', 
                    'price_id' => 'price_TestExtraClientsPriceID',
                ],
            ],

            'live' => [
                'freelancer' => [
                    'product_id' => 'prod_Qu6hjkoWOhNiZK', 
                    'price_id' => 'price_1Q2IToEEh64CES4Eg5xIuPOH',
                ],
                'freelancer_pro' => [
                    'product_id' => 'prod_ProPlanID', 
                    'price_id' => 'price_ProPlanPriceID',
                ],
                'extra_clients' => [
                    'product_id' => 'prod_LiveExtraClientsID', 
                    'price_id' => 'price_LiveExtraClientsPriceID',
                ],
            ],
        ],
    ],
];
