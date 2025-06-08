<?php

return [

    /**
     * Consumer Key of the App from developer.safaricom.co.ke
     */
    'consumerKey' => getenv('MPESA_CONSUMER_KEY'),

    /**
     * Consumer Secret of the App from developer.safaricom.co.ke
     */
    'consumerSecret' => getenv('MPESA_CONSUMER_SECRET'),

    /**
     * CallBackURL:
     * A CallBack URL is a valid secure URL that is used to receive notifications from M-Pesa API.
     * It is the endpoint to which the results will be sent by M-Pesa API.
     */
    'callBackURL' => getenv('MPESA_CALLBACK_URL'),

    /**
     * BaseURL
     * Base URL for Mpesa API Calls
     */
    'baseUrl' => getenv('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),

    /**
     * Paybill Number
     */
    'paybillNumber' => getenv('MPESA_PAYBILL_NUMBER'),

    /**
     * Lipa na Mpesa Shortcode (Paybill or Till Number)
     */
    'lipaNaMpesaShortcode' => getenv('LIPA_NA_MPESA_SHORTCODE'),

    /**
     * Lipa na Mpesa Callback URL
     */
    'lipaNaMpesaCallbackURL' => getenv('LIPA_NA_MPESA_CALLBACK_URL'),

    /**
     * Lipa na Mpesa Passkey
     */
    'lipaNaMpesaPasskey' => getenv('LIPA_NA_MPESA_PASSKEY'),

    /**
     * C2B Confirmation URL
     */
    'confirmationURL' => getenv('MPESA_CONFIRMATION_URL'),

    /**
     * C2B Validation URL
     */
    'validationURL' => getenv('MPESA_VALIDATION_URL'),

    /**
     * Mpesa Initiator Username
     */
    'initiatorUsername' => getenv('MPESA_INITIATOR_USERNAME'),

    /**
     * Mpesa Initiator Password
     */
    'initiatorPassword' => getenv('MPESA_INITIATOR_PASSWORD'),

    /**
     * Environment
     */
    'environment' => getenv('MPESA_ENVIRONMENT', 'sandbox'),

    /**
     * QueueTimeOutURL
     */
    'queueTimeOutURL' => getenv('MPESA_QUEUE_TIMEOUT_URL'),

    /**
     * ResultURL
     */
    'resultURL' => getenv('MPESA_RESULT_URL'),

];
