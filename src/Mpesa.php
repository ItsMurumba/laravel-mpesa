<?php

namespace Itsmurumba\Mpesa;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
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
    protected $callBackURL;

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
    protected $çç;

    /**
     * Lipa na Mpesa Passkey
     */
    protected $lipaNaMpesaPasskey;

    /**
     * C2B Confirmation URL
     */
    protected $confirmationURL;

    /**
     * C2B Validation URL
     */
    protected $validationURL;

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

    /**
     * This is the URL to be specified in your request that will be used
     * by API Proxy to send notification incase the payment
     * request is timed out while awaiting processing in the queue.
     */
    protected $queueTimeOutURL;

    /**
     * This is the URL to be specified in your request that will be used
     * by M-Pesa to send notification upon processing of the payment request.
     */
    protected $resultURL;

    protected $lipaNaMpesaCallbackURL;

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
        $this->setConfirmationURL();
        $this->setValidationURL();
        $this->setInitiatorUsername();
        $this->setInitiatorPassword();
        $this->setEnvironment();
        $this->setQueueTimeOutURL();
        $this->setResultURL();
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
        $this->payBillNumber = Config::get('mpesa.paybillNumber');
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
    public function setConfirmationURL()
    {
        $this->confirmationURL = Config::get('mpesa.confirmationURL');
    }

    /**
     * Get validation URL from Mpesa config file
     * This is the URL that receives the validation request from API upon payment submission.
     * The validation URL is only called if the external validation on the registered shortcode is enabled.
     * (By default External Validation is dissabled)
     *
     * @return void
     */
    public function setValidationURL()
    {
        $this->validationURL = Config::get('mpesa.validationURL');
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
     * Get the QueueTimeOutURL from Mpesa config file
     *
     * @return void
     */
    public function setQueueTimeOutURL()
    {
        $this->queueTimeOutURL = Config::get('mpesa.queueTimeOutURL');
    }

    /**
     * Get the ResultURL from Mpesa config file
     *
     * @return void
     */
    public function setResultURL()
    {
        $this->resultURL = Config::get('mpesa.resultURL');
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

        $authBearer = 'Bearer '.$accessToken;

        $this->client = new Client(
            [
                'base_uri' => $this->baseUrl,
                'headers' => [
                    'Authorization' => $authBearer,
                    'Content-Type' => 'application/json',
                ],
            ]
        );
    }

    /**
     * setHttpResponse to handle all the API request responses
     *
     * @param [type] $relativeUrl
     * @param [type] $method
     * @param  array  $body
     * @return void
     */
    private function setHttpResponse($relativeUrl, $method, $data)
    {

        if (is_null($method)) {
            throw new IsNullException('Empty method not allowed');
        }

        $response = $this->client->{strtolower($method)}(
            $this->baseUrl.$relativeUrl,
            [
                'body' => json_encode($data),
            ]
        );

        return $response->getBody()->getContents();
    }

    /**
     * Get Access Token to be used in allowed API requests
     */
    private function getAccessToken()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Basic '.base64_encode($this->consumerKey.':'.$this->consumerSecret),
        ])->get(
            $this->baseUrl.'/oauth/v1/generate?grant_type=client_credentials'
        );

        if ($response->status() == 200) {
            $response = json_decode($response);

            $this->expiresIn = date('Y-m-d H:i:s', (time() + $response->expires_in));
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
            $publicKey = File::get(__DIR__.'/certificates/production.cer');
        } else {
            $publicKey = File::get(__DIR__.'/certificates/sandbox.cer');
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
     * @param  string  $accountReference
     * @param  string  $transactionDescription
     * @return void
     */
    public function expressPayment($amount, $phoneNumber, $accountReference = 'CompanyXLTD', $transactionDescription = 'Payment of X')
    {
        $timestamp = date('YmdHis');

        $arrayData = [
            'BusinessShortCode' => $this->lipaNaMpesaShortcode,
            'Password' => base64_encode($this->lipaNaMpesaShortcode.$this->lipaNaMpesaPasskey.$timestamp),
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'PartyB' => $this->lipaNaMpesaShortcode,
            'PhoneNumber' => $phoneNumber,
            'CallBackURL' => $this->lipaNaMpesaCallbackURL,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDescription,
        ];

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
    public function expressPaymentQuery($checkoutRequestId)
    {
        $timestamp = date('YmdHis');

        $arrayData = [
            'BusinessShortCode' => $this->lipaNaMpesaShortcode,
            'Password' => base64_encode($this->lipaNaMpesaShortcode.$this->lipaNaMpesaPasskey.$timestamp),
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $checkoutRequestId,
        ];

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
        $arrayData = [
            'ShortCode' => $this->lipaNaMpesaShortcode,
            'ResponseType' => 'Completed',
            'ConfirmationURL' => $this->confirmationURL,
            'ValidationURL' => $this->validationURL,
        ];

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
    public function c2bPayment($commandId, $amount, $phoneNumber, $billRefNumber)
    {
        $arrayData = [
            'ShortCode' => $this->lipaNaMpesaShortcode,
            'CommandID' => $commandId,
            'amount' => $amount,
            'MSISDN' => $phoneNumber,
            'BillRefNumber' => $billRefNumber,
        ];

        $response = $this->setHttpResponse('/mpesa/c2b/v1/simulate', 'POST', $arrayData);

        return $response;
    }

    /**
     * Business To Customer (B2C):
     * Transact between an M-Pesa short code to a phone number registered on M-Pesa
     *
     * @param [type] $commandId
     * @param [type] $amount
     * @param [type] $phoneNumber
     * @param [type] $remarks
     * @param  string  $occassion
     * @return void
     */
    public function b2cPayment($commandId, $amount, $phoneNumber, $remarks, $occassion = '')
    {
        $arrayData = [
            'InitiatorName' => $this->initiatorUsername,
            'SecurityCredential' => $this->setSecurityCredentials(),
            'CommandID' => $commandId,
            'Amount' => $amount,
            'PartyA' => $this->lipaNaMpesaShortcode,
            'PartyB' => $phoneNumber,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $this->queueTimeOutURL,
            'ResultURL' => $this->resultURL,
            'Occassion' => $occassion,
        ];

        $response = $this->setHttpResponse('/mpesa/b2c/v1/paymentrequest', 'POST', $arrayData);

        return $response;
    }

    /**
     * Transaction Status:
     * Check the status of a transaction
     *
     * @param [type] $commandId
     * @param [type] $transactionId
     * @param [type] $identifierType
     * @param [type] $remarks
     * @param  string  $occassion
     * @return void
     */
    public function transactionStatus($transactionId, $identifierType, $remarks, $occassion = '')
    {
        $arrayData = [
            'Initiator' => $this->initiatorUsername,
            'SecurityCredential' => $this->setSecurityCredentials(),
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => $transactionId,
            'PartyA' => $this->lipaNaMpesaShortcode,
            'IdentifierType' => $identifierType,
            'ResultURL' => $this->resultURL,
            'QueueTimeOutURL' => $this->queueTimeOutURL,
            'Remarks' => $remarks,
            'Occassion' => $occassion,
        ];

        $response = $this->setHttpResponse('/mpesa/transactionstatus/v1/query', 'POST', $arrayData);

        return $response;
    }

    /**
     * Account Balance:
     * Enquire the balance on an M-Pesa BuyGoods (Till Number)
     *
     * @param [type] $identifierType
     * @param [type] $remarks
     * @return void
     */
    public function accountBalance($identifierType, $remarks)
    {
        $arrayData = [
            'Initiator' => $this->initiatorUsername,
            'SecurityCredential' => $this->setSecurityCredentials(),
            'CommandID' => 'AccountBalance',
            'PartyA' => $this->lipaNaMpesaShortcode,
            'IdentifierType' => $identifierType,
            'ResultURL' => $this->resultURL,
            'QueueTimeOutURL' => $this->queueTimeOutURL,
            'Remarks' => $remarks,
        ];

        $response = $this->setHttpResponse('/mpesa/accountbalance/v1/query', 'POST', $arrayData);

        return $response;
    }

    /**
     * Reversals:
     * Reverses an M-Pesa transaction.
     *
     * @param [type] $transactionId
     * @param [type] $amount
     * @param [type] $receiverParty
     * @param [type] $receiverIdentifierType
     * @param [type] $remarks
     * @param  string  $occasion
     * @return void
     */
    public function reversals($transactionId, $amount, $receiverParty, $receiverIdentifierType, $remarks, $occasion = '')
    {
        $arrayData = [
            'Initiator' => $this->initiatorUsername,
            'SecurityCredential' => $this->setSecurityCredentials(),
            'CommandID' => 'TransactionReversal',
            'TransactionID' => $transactionId,
            'Amount' => $amount,
            'ReceiverParty' => $receiverParty,
            'ReceiverIdentifierType' => $receiverIdentifierType,
            'ResultURL' => $this->resultURL,
            'QueueTimeOutURL' => $this->queueTimeOutURL,
            'Remarks' => $remarks,
            'Occasion' => $occasion,
        ];

        $response = $this->setHttpResponse('/mpesa/reversal/v1/request', 'POST', $arrayData);

        return $response;
    }

    /**
     * The Business to Business (B2B) API:
     * used to transfer money from one business to another business.
     *
     * @param [type] $commandId
     * @param [type] $amount
     * @param [type] $receiverShortcode
     * @param [type] $accountReference
     * @param [type] $remarks
     * @return void
     */
    public function b2bPayment($commandId, $amount, $receiverShortcode, $accountReference, $remarks)
    {
        $arrayData = [
            'InitiatorName' => $this->initiatorUsername,
            'SecurityCredential' => $this->setSecurityCredentials(),
            'CommandID' => $commandId,
            'SenderIdentifierType' => 'Shortcode',
            'RecieverIdentifierType' => 'Shortcode',
            'Amount' => $amount,
            'PartyA' => $this->lipaNaMpesaShortcode,
            'PartyB' => $receiverShortcode,
            'AccountReference' => $accountReference,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => $this->queueTimeOutURL,
            'ResultURL' => $this->resultURL,
        ];

        $response = $this->setHttpResponse('/mpesa/b2b/v1/paymentrequest', 'POST', $arrayData);

        return $response;
    }

    /**
     * Generate a dynamic QR code for M-Pesa payments
     *
     * @param  string  $merchantName The name of the merchant/business
     * @param  string  $transactionReference A unique reference for the transaction
     * @param  float  $amount The amount to be paid
     * @param  string  $transactionType Type of transaction (BG: Buy Goods, WA: Withdraw Agent, PB: Pay Bill, SM: Send Money, SB: Send Business)
     * @param  string  $creditPartyIdentifier The till number, paybill, or phone number receiving payment
     * @param  string  $size The size of the QR code in pixels (default: 300)
     * @return string JSON response containing the QR code data
     *
     * @throws InvalidArgumentException When an invalid transaction type is provided
     */
    public function generateDynamicQRCode($merchantName, $transactionReference, $amount, $transactionType, $creditPartyIdentifier, $size = '300')
    {
        $supportedTransactionTypes = ['BG', 'WA', 'PB', 'SM', 'SB'];

        if (! in_array($transactionType, $supportedTransactionTypes)) {
            throw new InvalidArgumentException('Invalid transaction type. Supported types are: '.implode(', ', $supportedTransactionTypes));
        }

        $data = [
            'MerchantName' => $merchantName,
            'RefNo' => $transactionReference,
            'Amount' => $amount,
            'TrxCode' => $transactionType,
            'CPI' => $creditPartyIdentifier,
            'Size' => $size,
        ];

        $response = $this->setHttpResponse('/mpesa/qrcode/v1/generate', 'POST', $data);

        return $response;
    }

    /**
     * Create M-Pesa Standing Order (M-Pesa Ratiba)
     *
     * Initiates a standing order request that allows automatic recurring payments from a customer to a business.
     *
     * @param  string  $orderName Unique name for the standing order
     * @param  string  $startDate Start date in YYYYMMDD format
     * @param  string  $endDate End date in YYYYMMDD format
     * @param  string  $amount The amount to be deducted in each transaction (whole numbers only)
     * @param  string  $phoneNumber Customer's phone number in format 254XXXXXXXXX
     * @param  string  $accountReference Account identifier for PayBill transactions (max 12 chars)
     * @param  string  $transactionDesc Description of the transaction (max 13 chars)
     * @param  string  $frequency Payment frequency: 1(One-Off), 2(Daily), 3(Weekly), 4(Monthly), 5(Bi-Monthly), 6(Quarterly), 7(Half-Year), 8(Yearly)
     * @param  string  $transactionType Either "Standing Order Customer Pay Bill" or "Standing Order Customer Pay Marchant"
     * @param  string  $receiverPartyIdentifierType "4" for PayBill or "2" for Till Number
     * @return string JSON response containing the standing order creation status
     *
     * @throws InvalidArgumentException When invalid frequency or transaction type is provided
     */
    public function createRatibaStandingOrder(
        $orderName,
        $startDate,
        $endDate,
        $businessShortCode,
        $amount,
        $phoneNumber,
        $callBackURL,
        $accountReference,
        $transactionDesc,
        $frequency,
        $transactionType,
        $receiverPartyIdentifierType
    ) {
        $validFrequencies = ['1', '2', '3', '4', '5', '6', '7', '8'];
        $validReceiverPartyIdentifierType = ['2', '4'];

        if (! in_array($frequency, $validFrequencies)) {
            throw new InvalidArgumentException('Invalid frequency. Supported values are: '.implode(', ', $validFrequencies));
        }

        if (! in_array($receiverPartyIdentifierType, $validReceiverPartyIdentifierType)) {
            throw new InvalidArgumentException('Invalid frequency. Supported values are: '.implode(', ', $validReceiverPartyIdentifierType));
        }

        $data = [
            'StandingOrderName' => $orderName,
            'StartDate' => $startDate,
            'EndDate' => $endDate,
            'BusinessShortCode' => $businessShortCode ?? $this->lipaNaMpesaShortcode,
            'TransactionType' => $transactionType,
            'ReceiverPartyIdentifierType' => $receiverPartyIdentifierType,
            'Amount' => $amount,
            'PartyA' => $phoneNumber,
            'CallBackURL' => $callBackURL ?? $this->callBackURL,
            'AccountReference' => $accountReference,
            'TransactionDesc' => $transactionDesc,
            'Frequency' => $frequency,
        ];

        $response = $this->setHttpResponse('/standingorder/v1/createStandingOrderExternal', 'POST', $data);

        return $response;
    }

    /**
     * Opt-in to M-PESA Bill Manager for organizations.
     * This API is used to opt-in as a biller to the M-PESA Bill Manager features.
     *
     * @param  string  $shortcode The organization's shortcode (Paybill or Buygoods) used to identify the organization and receive transactions.
     * @param  string  $email The official contact email address for the organization signing up to bill manager.
     * @param  string  $officialContact The official contact phone number for the organization signing up to bill manager.
     * @param  int  $sendReminders Flag to enable or disable SMS payment reminders for invoices sent.
     * @param  string  $logo Image to be embedded in the invoices and receipts sent to the customer.
     * @param  string  $callbackURL The callback URL to be invoked by the payments API to push payments done to the paybill.
     * @return array JSON response containing the opt-in status
     */
    public function billManagerOptInTo(
        $shortcode,
        $email,
        $officialContact,
        $sendReminders,
        $logo,
        $callbackURL = null
    ) {
        $data = [
            'shortcode' => $shortcode ?? $this->lipaNaMpesaShortcode,
            'email' => $email,
            'officialContact' => $officialContact,
            'sendReminders' => $sendReminders,
            'logo' => $logo,
            'callbackurl' => $callbackURL ?? $this->lipaNaMpesaCallbackURL,
        ];

        $response = $this->setHttpResponse('/billmanager-invoice/optin', 'POST', $data);

        return $response;
    }

    /**
     * Single Invoicing - Generic API for sending customized individual e-invoices.
     * This API enables you to create and send e-invoices to your customers.
     *
     * @param  string  $externalReference Unique invoice name for referencing an invoice.
     * @param  string  $billedFullName The name of the recipient to receive the invoice details.
     * @param  string  $billedPhoneNumber The phone number to receive invoice details via SMS.
     * @param  string  $billedPeriod Month and Year of the billed period.
     * @param  string  $invoiceName A descriptive invoice name for what your customer is being billed.
     * @param  string  $dueDate The date you expect the customer to have paid the invoice amount.
     * @param  string  $accountReference The account number being invoiced that uniquely identifies a customer.
     * @param  string  $amount Total Invoice amount to be paid in Kenyan Shillings.
     * @param  array  $invoiceItems Additional billable items to be included in the invoice.
     * @return array JSON response containing the invoice sending status
     */
    public function billManagerSingleInvoicing(
        $externalReference,
        $billedFullName,
        $billedPhoneNumber,
        $billedPeriod,
        $invoiceName,
        $dueDate,
        $accountReference,
        $amount,
        $invoiceItems = []
    ) {
        $data = [
            'externalReference' => $externalReference,
            'billedFullName' => $billedFullName,
            'billedPhoneNumber' => $billedPhoneNumber,
            'billedPeriod' => $billedPeriod,
            'invoiceName' => $invoiceName,
            'dueDate' => $dueDate,
            'accountReference' => $accountReference,
            'amount' => $amount,
            'invoiceItems' => $invoiceItems,
        ];

        $response = $this->setHttpResponse('/billmanager-invoice/single-invoicing', 'POST', $data);

        return $response;
    }

    /**
     * Bulk Invoicing - Generic API for sending multiple e-invoices to customers.
     * This API enables you to create and send e-invoices to your customers in bulk.
     *
     * @param array $invoices A collection of invoices to be sent in bulk.
     * Each invoice should contain the following keys:
     *                          - externalReference: Unique invoice name for referencing an invoice.
     *                          - billedFullName: The name of the recipient to receive the invoice details.
     *                          - billedPhoneNumber: The phone number to receive invoice details via SMS.
     *                          - billedPeriod: Month and Year of the billed period.
     *                          - invoiceName: A descriptive invoice name for what your customer is being billed.
     *                          - dueDate: The date you expect the customer to have paid the invoice amount.
     *                          - accountReference: The account number being invoiced that uniquely identifies a customer.
     *                          - amount: Total Invoice amount to be paid in Kenyan Shillings.
     *                          - invoiceItems: Additional billable items to be included in the invoice.
     * @return array JSON response containing the bulk invoice sending status
     */
    public function billManagerBulkInvoicing($invoices)
    {
        $data = $invoices;

        $response = $this->setHttpResponse('/billmanager-invoice/bulk-invoicing', 'POST', $data);

        return $response;
    }

    /**
     * Payments and Reconciliation - Generic API for processing payments and sending e-receipts.
     * This API enables your customers to receive e-receipts for payments made to your paybill account.
     *
     * @param string $transactionId The M-PESA generated reference.
     * @param numeric $paidAmount Amount Paid In KES.
     * @param numeric $msisdn The customers PhoneNumber debited.
     * @param string $dateCreated The date the payment was done and recorded in the BillManager System.
     * @param string $accountReference This is the account number being invoiced that uniquely identifies a customer.
     * @param numeric $shortCode This is organizations shortcode (Paybill or Buygoods - A 5 to 6 digit account number) used to identify an organization and receive the transaction.
     * @return array JSON response containing the payment processing status
     */
    public function billManagerPaymentReconciliation(
        $transactionId,
        $paidAmount,
        $customerPhoneNumber,
        $dateCreated,
        $accountReference,
        $shortCode
    ) {
        $data = [
            'transactionId' => $transactionId,
            'paidAmount' => $paidAmount,
            'msisdn' => $customerPhoneNumber,
            'dateCreated' => $dateCreated,
            'accountReference' => $accountReference,
            'shortCode' => $shortCode ?? $this->lipaNaMpesaShortcode,
        ];

        $response = $this->setHttpResponse('/billmanager-invoice/reconciliation', 'POST', $data);

        return $response;
    }
}
