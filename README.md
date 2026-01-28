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

## Multi-tenant / multiple paybills & tills (Profiles)

For SaaS / multi-merchant use cases, you can define multiple Mpesa **profiles** in `config/mpesa.php` (under `profiles`) and pick one at runtime.

```php
// config/mpesa.php
return [
    'default_profile' => env('MPESA_DEFAULT_PROFILE', 'default'),

    'profiles' => [
        'default' => [
            'consumerKey' => env('MPESA_CONSUMER_KEY'),
            'consumerSecret' => env('MPESA_CONSUMER_SECRET'),
            'lipaNaMpesaShortcode' => env('LIPA_NA_MPESA_SHORTCODE'),
            'lipaNaMpesaPasskey' => env('LIPA_NA_MPESA_PASSKEY'),
            // ...
        ],

        'tenant_a' => [
            'consumerKey' => env('TENANT_A_MPESA_CONSUMER_KEY'),
            'consumerSecret' => env('TENANT_A_MPESA_CONSUMER_SECRET'),
            'lipaNaMpesaShortcode' => env('TENANT_A_LIPA_NA_MPESA_SHORTCODE'),
            'lipaNaMpesaPasskey' => env('TENANT_A_LIPA_NA_MPESA_PASSKEY'),
        ],
    ],
];
```

# Usage

## Single Paybill/Till Setup (Traditional)

For applications with a single Mpesa account:

```php
use Itsmurumba\Mpesa\Mpesa;

// Direct instantiation
$mpesa = new Mpesa();
$mpesa->expressPayment($amount, $phoneNumber);

// Or via constructor injection
class PaymentController
{
    protected $mpesa;

    public function __construct()
    {
        $this->mpesa = new Mpesa();
    }
}
```

## Multi-tenant / SaaS Setup

For SaaS platforms where each customer has their own Mpesa account:

### Option 1: Database-driven (Recommended)

**Setup:**
```bash
# Publish and run migration
php artisan vendor:publish --tag=mpesa-migrations
php artisan migrate

# Enable in .env
MPESA_USE_DATABASE=true
```

**Store tenant profiles:**
```php
DB::table('mpesa_profiles')->insert([
    'name' => 'tenant-slug-or-id',
    'consumer_key' => 'key',
    'consumer_secret' => 'secret',
    'lipa_na_mpesa_shortcode' => '123456',
    'lipa_na_mpesa_passkey' => 'passkey',
    'environment' => 'sandbox',
    'is_active' => true,
    // ... other fields
]);
```

**Usage:**
```php
use Itsmurumba\Mpesa\Facades\Mpesa;

// Use tenant-specific profile
Mpesa::for('tenant-slug-or-id')->expressPayment($amount, $phoneNumber);
Mpesa::for('tenant-slug-or-id')->b2cPayment(...);
```

### Option 2: Config-based (Static profiles)

For a small, fixed number of tenants, define in `config/mpesa.php`:

```php
return [
    'profiles' => [
        'tenant_a' => [
            'consumerKey' => env('TENANT_A_CONSUMER_KEY'),
            'consumerSecret' => env('TENANT_A_CONSUMER_SECRET'),
            'lipaNaMpesaShortcode' => env('TENANT_A_SHORTCODE'),
            // ...
        ],
    ],
];
```

**Usage:**
```php
use Itsmurumba\Mpesa\Facades\Mpesa;

Mpesa::for('tenant_a')->expressPayment($amount, $phoneNumber);
```

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








