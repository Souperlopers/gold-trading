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

    'otp' => [
        'url'                => env('OTP_API_URL'        ),
        'api_key'            => env('OTP_API_KEY'        ),
        'template_id'        => env('OTP_TEMPLATE_ID'    ),
        'timeout'            => env('OTP_TIMEOUT'        ),
        'connection_timeout' => env('OTP_CONNECT_TIMEOUT'),
    ],

    'national_id' => [
        'url' => env('NATIONAL_ID_URL'),
        'api_key' => env('NATIONAL_ID_API_KEY'),
    ],
];
