# Introduction
This is a Laravel package for Safaricom Mpesa Daraja API. It includes all public available endpoints.

# Installation

Laravel 5.5+ :
````
composer require itsmurumba/laravel-mpesa
````

Laravel =<5.4 :

After running the composer install above, you should add a service provider and alias of the package in config/app.php file.

````
Itsmurumba\Mpesa\MpesaServiceProvider::class
````
# Configurations

1. Add the following to you .env file

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

1. 


# Contribution
This is a community package and thus welcome anyone intrested to contribute in improving the package. Kindly go through the [Contribution.md](Contribution.md) before starting to contribute. Keep those PRs and Issues coming.

# Buy Me Coffee
Give this repo a star and i will have my super powers recharged. You can also follow me on twitter [@ItsMurumba](https://twitter.com/ItsMurumba)

# License
This package is licensed under the MIT License. Please review the [License](LICENSE.md) file for details








