# Introduction
This is a Laravel package for Safaricom Mpesa Daraja API. It includes all public available endpoints:

* Consumer to Business (C2B) payments
* Business to Consumer (B2C) payments
* Mpesa Express Payment (Lipa Na Mpesa Online)
* Transaction Status
* Account Balance
* Reversal
* Business to Business (B2B) payments

# Installation

Run the following command to install Laravel Mpesa package in your Laravel project:

````
composer require itsmurumba/laravel-mpesa
````

If you are using **Laravel 5.5** and above, skip to the [**Configurations**](https://github.com/ItsMurumba/laravel-mpesa#configurations) step.

After running the composer require above, you should add a service provider and alias of the package in config/app.php file.(For Laravel 5.4 and below)

````
Itsmurumba\Mpesa\MpesaServiceProvider::class
````

# Configurations

After installing the package, run the following command to install `mpesa.php` configuartion file in the `config` folder:

````
php artisan mpesa:install
````

or 
````
php artisan vendor:publish
````

Add and define the following variables in your `.env` file

````
MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_CALLBACK_URL=
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_PAYBILL_NUMBER=600978
LIPA_NA_MPESA_SHORTCODE=174379
LIPA_NA_MPESA_CALLBACK_URL=
LIPA_NA_MPESA_PASSKEY=
MPESA_CONFIRMATION_URL=
MPESA_VALIDATION_URL=
MPESA_INITIATOR_USERNAME=
MPESA_INITIATOR_PASSWORD=
MPESA_ENVIRONMENT=sandbox
MPESA_QUEUE_TIMEOUT_URL=
MPESA_RESULT_URL=
````

# Usage
Add the following constructor inside your controller:
`````
protected $mpesa;

public function __construct(){
    $this->mpesa = new Mpesa();
}
`````

**1. Mpesa Express Payment (Lipa Na Mpesa Online)**
````
$this->mpesa->expressPayment($amount, $phoneNumber, $accountReference = 'CompanyXLTD', $transactionDescription = 'Payment of X');
````
* $phoneNumber = 254XXXXXXXXX
* $accountReference = Account Reference (maximum 12 characters)
* transactionDescription = Transaction Description (1-13 characters)

**2. Mpesa Express Payment Query (Lipa Na Mpesa Online)**
````
$this->mpesa->expressPaymentQuery($checkoutRequestId);
````
**3. C2B Register URLs**
````
$this->mpesa->c2bRegisterURLs();
````

**4. Consumer to Business (C2B) payments**
````
$this->mpesa->c2bPaymentc2bPayment($commandId, $amount, $phoneNumber, $billRefNumber); 
````
* $commandId = can only be set to **CustomerPayBillOnline** or **CustomerBuyGoodsOnline**
* $billRefNumber = used on CustomerPayBillOnline option only e.g an Account Number. Set the value to `''` when commandId is **CustomerBuyGoodsOnline**

**5. Business to Consumer (B2C) payments**
````
$this->mpesa->b2cPayment($commandId, $amount, $phoneNumber, $remarks, $occassion = '');
````
**6. Transaction Status**
````
$this->mpesa->transactionStatus($transactionId, $identifierType, $remarks, $occassion = '');
````
**7. Account Balance**
````
$this->mpesa->accountBalance($identifierType, $remarks);
````
**8. Reversal**
````
$this->mpesa->reversals($transactionId, $amount, $receiverParty, $receiverIdentifierType, $remarks, $occasion = '');
````
**9. Business to Business Payment(B2B)**
````
$this->mpesa->b2bPayment($commandId, $amount, $receiverShortcode, $accountReference, $remarks);
````


# Contribution
This is a community package and thus welcome anyone intrested to contribute in improving the package. Kindly go through the [Contribution.md](Contribution.md) before starting to contribute. Keep those PRs and Issues coming.

# Buy Me Coffee
Give this repo a star and i will have my super powers recharged. You can also follow me on twitter [@ItsMurumba](https://twitter.com/ItsMurumba)

# License
This package is licensed under the MIT License. Please review the [License](LICENSE.md) file for details








