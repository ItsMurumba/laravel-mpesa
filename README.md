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
1. Mpesa Express Payment (Lipa Na Mpesa Online)
````
$mpesa = new Mpesa();
$mpesa->expressPayment(1,254720000000,'Test Payment');

1 = amount
254720000000 = phone number
'CompanyXLTD' = Account Reference
'Test Payment' = Transaction Description
````
2. Mpesa Express Payment Query (Lipa Na Mpesa Online)
3. C2B Register URLs
4. Consumer to Business (C2B) payments
5. Business to Consumer (B2C) payments
6. Transaction Status
7. Account Balance
8. Reversal
9. Business to Business Payment(B2B)


# Contribution
This is a community package and thus welcome anyone intrested to contribute in improving the package. Kindly go through the [Contribution.md](Contribution.md) before starting to contribute. Keep those PRs and Issues coming.

# Buy Me Coffee
Give this repo a star and i will have my super powers recharged. You can also follow me on twitter [@ItsMurumba](https://twitter.com/ItsMurumba)

# License
This package is licensed under the MIT License. Please review the [License](LICENSE.md) file for details








