<?php

namespace Itsmurumba\Mpesa;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Itsmurumba\Mpesa\Exceptions\IsNullException;

class Mpesa
{

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

    /**
     * Mpesa Paybill Number
     */
    protected $payBillNumber;

    /**
     * CallBackURL
     */
    protected $callbackUrl;

    /**
     * Access token from Mpesa for authentication in subsequent requests
     */
    protected $accessToken;

    /**
     * Expiry time for the access token
     *
     * @var [type]
     */
    protected $expiresIn;



    public function __construct()
    {
        $this->setConsumerKey();
        $this->setConsumerSecret();
        $this->setBaseUrl();
        $this->setPayBillNumber();
        $this->setCallBackURL();
        $this->setRequestOptions();
    }

    /**
     * Get the consumer key from mpesa config file.
     */
    public function setConsumerKey()
    {
        $this->consumerKey = Config::get('mpesa.consumerKey');
    }

    /**
     * Get the consumer secret from mpesa config file.
     */
    public function setConsumerSecret()
    {
        $this->consumerSecret = Config::get('mpesa.consumerSecret');
    }

    /**
     * Get the Base URL from Mpesa config file
     */
    public function setBaseUrl()
    {
        $this->baseUrl = Config::get('mpesa.baseUrl');
    }

    /**
     * Get the Paybill Number from Mpesa config file
     */
    public function setPaybillNumber()
    {
        $this->paybillNumber = Config::get('mpesa.paybillNumber');
    }

    /**
     * Get the CallBackURL from Mpesa config file
     */
    public function setCallBackURL()
    {
        $this->callBackURL = Config::get('mpesa.callBackURL');
    }

    /**
     * Set options for making the Client requests.
     */
    private function setRequestOptions()
    {

        if (isset($this->accessToken) && strtotime($this->expiresIn) > time()) {
            $accessToken = $this->accessToken;
        } else {
            $accessToken = $this->getAccessToken();
        }

        $authBearer = 'Bearer ' . $accessToken;

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

    /**
     * setHttpResponse to handle all the API request responses
     *
     * @param [type] $relativeUrl
     * @param [type] $method
     * @param array $body
     * @return void
     */
    private function setHttpResponse($relativeUrl, $method, $body = [])
    {

        if (is_null($method)) {
            throw new IsNullException("Empty method not allowed");
        }

        $this->response = $this->client->{strtolower($method)}(
            $this->baseUrl . $relativeUrl,
            ["body" => json_encode($body)]
        );

        return $this;
    }

    /**
     * Get Access Token to be used in allowed API requests
     */
    private function getAccessToken()
    {
        $response = Http::withToken(base64_encode($this->consumerKey . ':' . $this->consumerSecret))
            ->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');

        if ($response->status() == 200) {

            $this->expiresIn =  date('Y-m-d H:i:s', (time() + $response->expires_in));
            $this->accessToken = $response->access_token;

            return $response->access_token;
        } else {
            return false;
        }
    }

    public function express()
    {
    }
}
