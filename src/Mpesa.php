<?php

namespace Itsmurumba\Mpesa;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
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

    /**
     * Lipa na Mpesa Shortcode
     */
    protected $lipaNaMpesaShortcode;

    /**
     * Lipa na Mpesa Callback URL
     */
    protected $lipaNaMpesaCallbackURL;

    /**
     * Lipa na Mpesa Passkey
     */
    protected $lipaNaMpesaPasskey;

    /**
     * C2B Confirmation URL
     */
    protected $c2bConfirmationURL;

    /**
     * C2B Validation URL
     */
    protected $c2bvalidationURL;

    /**
     *  B2C Initiator Username
     */
    protected $initiatorUsername;

    /**
     * B2C Initiator Password
     */
    protected $initiatorPassword;

    /**
     * Get the active environment
     */
    protected $environment;



    public function __construct()
    {
        $this->setConsumerKey();
        $this->setConsumerSecret();
        $this->setBaseUrl();
        $this->setPayBillNumber();
        $this->setLipaNaMpesaShortcode();
        $this->setCallBackURL();
        $this->setLipaNaMpesaCallbackURL();
        $this->setLipaNaMpesaPasskey();
        $this->setC2BConfirmationURL();
        $this->setC2BValidationURL();
        $this->setInitiatorUsername();
        $this->setInitiatorPassword();
        $this->setEnvironment();
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
     * Get Lipa Na Mpesa Paybill or buygoods from Mpesa config file
     *
     * @return void
     */
    public function setLipaNaMpesaShortcode()
    {
        $this->lipaNaMpesaShortcode = Config::get('mpesa.lipaNaMpesaShortcode');
    }

    /**
     * Get the Lipa Na Mpesa Callback URL from Mpesa config file
     *
     * @return void
     */
    public function setLipaNaMpesaCallbackURL()
    {
        $this->lipaNaMpesaCallbackURL = Config::get('mpesa.lipaNaMpesaCallbackURL');
    }

    /**
     * Get the Lipa Na Mpesa Passkey from Mpesa config file
     *
     * @return void
     */
    public function setLipaNaMpesaPasskey()
    {
        $this->lipaNaMpesaPasskey = Config::get('mpesa.lipaNaMpesaPasskey');
    }

    /**
     * Get  C2B Confirmation URL from Mpesa config file
     * This is the URL that receives the confirmation request from API upon payment completion.
     *
     * @return void
     */
    public function setC2BConfirmationURL()
    {
        $this->c2BConfirmationURL = Config::get('mpesa.c2bConfirmationURL');
    }

    /**
     * Get C2B validation URL from Mpesa config file
     * This is the URL that receives the validation request from API upon payment submission.
     * The validation URL is only called if the external validation on the registered shortcode is enabled.
     * (By default External Validation is dissabled)
     *
     * @return void
     */
    public function setC2BValidationURL()
    {
        $this->c2bValidationURL = Config::get('mpesa.c2bValidationURL');
    }

    /**
     * Get the username of the Mpesa B2C account API operator from Mpesa config file
     *
     * @return void
     */
    private function setInitiatorUsername()
    {
        $this->initiatorUsername = Config::get('mpesa.initiatorUsername');
    }

    /**
     * Get the Password of the Mpesa B2C account API operator from Mpesa config file
     *
     * @return void
     */
    private function setInitiatorPassword()
    {
        $this->initiatorPassword = Config::get('mpesa.initiatorPassword');
    }

    /**
     * Get the set environment from Mpesa config file
     *
     * @return void
     */
    public function setEnvironment()
    {
        $this->environment = Config::get('mpesa.environment');
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

    /**
     * Encryption of Security credentials to be used in the following API calls:
     * B2C
     * B2B
     * Transaction Status Query API
     * Reversal API
     *
     * @return void
     */
    private function setSecurityCredentials()
    {
        if ($this->environment == 'production') {
            $publicKey = File::get(__DIR__ . '/certificates/production.cer');
        } else {
            $publicKey = File::get(__DIR__ . '/certificates/sandbox.cer');
        }

        openssl_public_encrypt($this->initiatorPassword, $encryptedData, $publicKey, OPENSSL_PKCS1_PADDING);

        $securityCredential = base64_encode($encryptedData);

        return $securityCredential;
    }

    /**
     * Mpesa Express: Initiates online payment on behalf of a customer.
     *
     * @param [type] $amount
     * @param [type] $phoneNumber
     * @param string $accountReference
     * @param string $transactionDescription
     * @return void
     */
    public function express($amount, $phoneNumber, $accountReference = 'CompanyXLTD', $transactionDescription = 'Payment of X')
    {
        $timestamp = date('YmdHis');

        $arrayData = array(
            "BusinessShortCode" => $this->lipaNaMpesaShortcode,
            "Password"  => base64_encode($this->lipaNaMpesaShortcode . $this->lipaNaMpesaPasskey . $timestamp),
            "Timestamp"  => $timestamp,
            "TransactionType"  => "CustomerPayBillOnline",
            "Amount"  => $amount,
            "PartyA"  => $phoneNumber,
            "PartyB"  => $this->lipaNaMpesaShortcode,
            "PhoneNumber"  => $phoneNumber,
            "CallBackURL"  => $this->lipaNaMpesaCallbackURL,
            "AccountReference"  => $accountReference,
            "TransactionDesc"  => $transactionDescription
        );

        $response = $this->setHttpResponse('/mpesa/stkpush/v1/processrequest', 'POST', $arrayData);

        return $response;
    }

    /**
     * M-Pesa Express Query:
     * Check the status of a Lipa Na M-Pesa Online Payment.
     *
     * @param [type] $checkoutRequestId
     * @return void
     */
    public function expressQuery($checkoutRequestId)
    {
        $timestamp = date('YmdHis');

        $arrayData = array(
            "BusinessShortCode" => $this->lipaNaMpesaShortcode,
            "Password" => base64_encode($this->lipaNaMpesaShortcode . $this->lipaNaMpesaPasskey . $timestamp),
            "Timestamp" => $timestamp,
            "CheckoutRequestID" => $checkoutRequestId
        );

        $response = $this->setHttpResponse('/mpesa/stkpushquery/v1/query', 'POST', $arrayData);

        return $response;
    }

    /**
     * Customer To Business Register URL:
     * Register validation and confirmation URLs on M-Pesa
     *
     * @return void
     */
    public function c2bRegisterURLs()
    {
        $arrayData = array(
            "ShortCode" => $this->payBillNumber,
            "ResponseType" => "Completed",
            "ConfirmationURL" => $this->c2bConfirmationURL,
            "ValidationURL" => $this->c2bvalidationURL
        );

        $response = $this->setHttpResponse('/mpesa/c2b/v1/registerurl', 'POST', $arrayData);

        return $response;
    }

    /**
     * Customer To Business Simulate:
     * Make payment requests from Client to Business (C2B)
     *
     * @param [type] $amount
     * @param [type] $phoneNumber
     * @param [type] $billRefNumber
     * @return void
     */
    public function c2bSimulation($amount, $phoneNumber, $billRefNumber)
    {
        $arrayData = array(
            "ShortCode" => $this->lipaNaMpesaShortcode,
            "CommandID" => "CustomerPayBillOnline",
            "amount" => $amount,
            "MSISDN" => $phoneNumber,
            "BillRefNumber" => $billRefNumber,
        );

        $response = $this->setHttpResponse('/mpesa/c2b/v1/simulate', 'POST', $arrayData);

        return $response;
    }
}
