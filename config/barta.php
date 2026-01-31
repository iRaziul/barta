<?php

// config for Larament/Barta
return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    | This is the provider Barta will use unless specified otherwise.
    */
    'default' => env('BARTA_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | SMS Drivers Configuration
    |--------------------------------------------------------------------------
    | Add your credentials for the providers here.
    */
    'drivers' => [
        'esms' => [
            'api_token' => env('BARTA_ESMS_TOKEN'),
            'sender_id' => env('BARTA_ESMS_SENDER_ID'),
        ],
        'mimsms' => [
            'username' => env('BARTA_MIMSMS_USERNAME'),
            'api_key' => env('BARTA_MIMSMS_API_KEY'),
            'sender_id' => env('BARTA_MIMSMS_SENDER_ID'),
        ],
        'ssl' => [
            'api_token' => env('BARTA_SSL_TOKEN'),
            'sender_id' => env('BARTA_SSL_SENDER_ID'),
            'csms_id' => env('BARTA_SSL_CSMS_ID'),
        ],
        'grameenphone' => [
            'username' => env('BARTA_GP_USERNAME'),
            'password' => env('BARTA_GP_PASSWORD'),
            'cli' => env('BARTA_GP_CLI', '2222'),
            'message_type' => env('BARTA_GP_MESSAGE_TYPE', 1),
        ],
        'banglalink' => [
            'user_id' => env('BARTA_BL_USER_ID'),
            'password' => env('BARTA_BL_PASSWORD'),
            'sender_id' => env('BARTA_BL_SENDER_ID'),
        ],
        'robi' => [
            'username' => env('BARTA_ROBI_USERNAME'),
            'password' => env('BARTA_ROBI_PASSWORD'),
        ],
        'infobip' => [
            'base_url' => env('BARTA_INFOBIP_BASE_URL'),
            'username' => env('BARTA_INFOBIP_USERNAME'),
            'password' => env('BARTA_INFOBIP_PASSWORD'),
            'sender_id' => env('BARTA_INFOBIP_SENDER_ID'),
        ],
        'adnsms' => [
            'api_key' => env('BARTA_ADNSMS_API_KEY'),
            'api_secret' => env('BARTA_ADNSMS_API_SECRET'),
            'sender_id' => env('BARTA_ADNSMS_SENDER_ID'),
            'request_type' => env('BARTA_ADNSMS_REQUEST_TYPE', 'SINGLE_SMS'),
            'message_type' => env('BARTA_ADNSMS_MESSAGE_TYPE', 'TEXT'),
        ],
        'alphasms' => [
            'api_key' => env('BARTA_ALPHASMS_API_KEY'),
            'sender_id' => env('BARTA_ALPHASMS_SENDER_ID'),
        ],
        'greenweb' => [
            'token' => env('BARTA_GREENWEB_TOKEN'),
        ],
        'bulksms' => [
            'api_key' => env('BARTA_BULKSMS_API_KEY'),
            'sender_id' => env('BARTA_BULKSMS_SENDER_ID'),
        ],
        'elitbuzz' => [
            'url' => env('BARTA_ELITBUZZ_URL'),
            'api_key' => env('BARTA_ELITBUZZ_API_KEY'),
            'sender_id' => env('BARTA_ELITBUZZ_SENDER_ID'),
            'type' => env('BARTA_ELITBUZZ_TYPE', 'text'),
        ],
        'smsnoc' => [
            'api_token' => env('BARTA_SMSNOC_TOKEN'),
            'sender_id' => env('BARTA_SMSNOC_SENDER_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Request Configuration
    |--------------------------------------------------------------------------
    | Set the timeout, retry, and retry delay for the HTTP client.
    */
    'request' => [
        'timeout' => env('BARTA_REQUEST_TIMEOUT', 10),
        'retry' => env('BARTA_REQUEST_RETRY', 3),
        'retry_delay' => env('BARTA_REQUEST_RETRY_DELAY', 300),
    ],
];
