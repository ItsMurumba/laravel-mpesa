<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Profile
    |--------------------------------------------------------------------------
    |
    | When using multi-tenant / multi-shortcode setups, you can define multiple
    | Mpesa profiles under `profiles` and choose which one to use at runtime.
    |
    | `Mpesa::for('tenant-a')` will use the profile with that key.
    |
    */
    'default_profile' => env('MPESA_DEFAULT_PROFILE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Use Database for Profiles
    |--------------------------------------------------------------------------
    |
    | When enabled, the package will check the `mpesa_profiles` table first
    | before falling back to config-based profiles. Useful for SaaS platforms
    | where tenants are added dynamically.
    |
    */
    'use_database' => env('MPESA_USE_DATABASE', false),

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
    | Lipa na Mpesa Transaction Type
    |--------------------------------------------------------------------------
    |
    | `CustomerPayBillOnline` for a paybill (default) or `CustomerBuyGoodsOnline`
    | for a Buy Goods till, as Daraja defines them. `BusinessShortCode` and the
    | password always use `lipaNaMpesaShortcode` and `lipaNaMpesaPasskey`.
    |
    */
    'lipaNaMpesaTransactionType' => env('LIPA_NA_MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),

    /*
    |--------------------------------------------------------------------------
    | Lipa na Mpesa Till Number
    |--------------------------------------------------------------------------
    |
    | Sent as `PartyB` in STK push, the shortcode that receives the money.
    | Leave empty when that's `lipaNaMpesaShortcode` itself (every paybill, and a
    | till with its own passkey). Set it when the till's passkey was issued to
    | another shortcode, such as the store or head office number.
    |
    */
    'lipaNaMpesaTillNumber' => env('LIPA_NA_MPESA_TILL_NUMBER'),

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
    | Mpesa Security Credential
    |--------------------------------------------------------------------------
    |
    | Optional pre-encrypted security credential (e.g. generated on the Daraja
    | portal). When set it is sent as-is instead of encrypting the initiator
    | password with Safaricom's certificate.
    |
    */
    'securityCredential' => env('MPESA_SECURITY_CREDENTIAL'),

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

    /*
    |--------------------------------------------------------------------------
    | Profiles (Multi-tenant)
    |--------------------------------------------------------------------------
    |
    | Define one or more Mpesa profiles. This is useful for SaaS platforms where
    | each customer has their own paybill/till, keys, callbacks, etc.
    |
    | The root-level keys above are the default profile. A named profile only
    | needs the keys that differ; the rest come from the root. Selecting a
    | profile that isn't defined here (or in the database) throws, unless the
    | call passes its own overrides.
    |
    */
    'profiles' => [
        // 'tenant_a' => [
        //     'consumerKey' => env('TENANT_A_MPESA_CONSUMER_KEY'),
        //     'consumerSecret' => env('TENANT_A_MPESA_CONSUMER_SECRET'),
        //     'lipaNaMpesaShortcode' => env('TENANT_A_LIPA_NA_MPESA_SHORTCODE'),
        //     'lipaNaMpesaPasskey' => env('TENANT_A_LIPA_NA_MPESA_PASSKEY'),
        // ],
    ],
];
