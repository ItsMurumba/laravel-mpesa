<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Consumer Key
    |--------------------------------------------------------------------------
    |
    | Consumer Key of the App from developer.safaricom.co.ke
    |
    */
    'consumerKey' => env('MPESA_CONSUMER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Consumer Secret
    |--------------------------------------------------------------------------
    |
    | Consumer Secret of the App from developer.safaricom.co.ke
    |
    */
    'consumerSecret' => env('MPESA_CONSUMER_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Callback URL
    |--------------------------------------------------------------------------
    |
    | A CallBack URL is a valid secure URL that is used to receive notifications
    | from M-Pesa API. It is the endpoint to which the results will be sent by
    | M-Pesa API.
    |
    */
    'callBackURL' => env('MPESA_CALLBACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL for Mpesa API Calls
    |
    */
    'baseUrl' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),

    /*
    |--------------------------------------------------------------------------
    | Paybill Number
    |--------------------------------------------------------------------------
    |
    | Your M-Pesa Paybill Number
    |
    */
    'paybillNumber' => env('MPESA_PAYBILL_NUMBER'),

    /*
    |--------------------------------------------------------------------------
    | Lipa na Mpesa Shortcode
    |--------------------------------------------------------------------------
    |
    | Lipa na Mpesa Shortcode (Paybill or Till Number)
    |
    */
    'lipaNaMpesaShortcode' => env('LIPA_NA_MPESA_SHORTCODE'),

    /*
    |--------------------------------------------------------------------------
    | Lipa na Mpesa Callback URL
    |--------------------------------------------------------------------------
    |
    | Callback URL for Lipa na Mpesa transactions
    |
    */
    'lipaNaMpesaCallbackURL' => env('LIPA_NA_MPESA_CALLBACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Lipa na Mpesa Passkey
    |--------------------------------------------------------------------------
    |
    | Passkey for Lipa na Mpesa transactions
    |
    */
    'lipaNaMpesaPasskey' => env('LIPA_NA_MPESA_PASSKEY'),

    /*
    |--------------------------------------------------------------------------
    | C2B Confirmation URL
    |--------------------------------------------------------------------------
    |
    | URL for C2B transaction confirmations
    |
    */
    'confirmationURL' => env('MPESA_CONFIRMATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | C2B Validation URL
    |--------------------------------------------------------------------------
    |
    | URL for C2B transaction validations
    |
    */
    'validationURL' => env('MPESA_VALIDATION_URL'),

    /*
    |--------------------------------------------------------------------------
    | Mpesa Initiator Username
    |--------------------------------------------------------------------------
    |
    | Username for initiating M-Pesa transactions
    |
    */
    'initiatorUsername' => env('MPESA_INITIATOR_USERNAME'),

    /*
    |--------------------------------------------------------------------------
    | Mpesa Initiator Password
    |--------------------------------------------------------------------------
    |
    | Password for initiating M-Pesa transactions
    |
    */
    'initiatorPassword' => env('MPESA_INITIATOR_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | M-Pesa API environment (sandbox or production)
    |
    */
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Queue Timeout URL
    |--------------------------------------------------------------------------
    |
    | URL for queue timeout notifications
    |
    */
    'queueTimeOutURL' => env('MPESA_QUEUE_TIMEOUT_URL'),

    /*
    |--------------------------------------------------------------------------
    | Result URL
    |--------------------------------------------------------------------------
    |
    | URL for transaction results
    |
    */
    'resultURL' => env('MPESA_RESULT_URL'),
];
