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


];






