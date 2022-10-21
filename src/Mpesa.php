<?php

namespace Itsmurumba\Mpesa;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;

class Mpesa{

    /**
     * Consumer Key from the app on developer.safaricom.co.ke
     */
    protected $consumerKey;

    /**
     * Consumer Secret from the app on developer.safaricom.co.ke
     */
    protected $consumerSecret;

    /**
     * Instance of Client
     *
     */
    protected $client;

    /**
     * Mpesa API base URL
     */
    protected $baseUrl;

    /**
     * Response from requests made to Mpesa
     */
    protected $response;

    public function __construct(){
        $this->setConsumerKey();
        $this->setConsumerSecret();
        $this->setBaseUrl();
        $this->setRequestOptions();
    }

    /**
     * Get the consumer key from mpesa config file.
     */
    public function setConsumerKey(){
        $this->consumerKey = Config::get('mpesa.consumerKey');
    }

    /**
     * Get the consumer secret from mpesa config file.
     */
    public function setConsumerSecret(){
        $this->consumerSecret = Config::get('mpesa.consumerSecret');
    }

    /**
     * Get the Base URL from Mpesa config file
     */
    public function setBaseUrl(){
        $this->baseUrl = Config::get('mpesa.baseUrl');
    }

    /**
     * Set options for making the Client requests.
     */
    private function setRequestOptions(){
        $authBearer = 'Bearer ' . base64_encode($this->consumerKey.':'.$this->consumerSecret);

        $this->client = new Client(
            [
                'base_uri' => $this->baseUrl,
                'headers' => [
                    'Authorization' => $authBearer,
                    'Content-Type' => 'application/json',
                ]
            ]
        );

    }
}






