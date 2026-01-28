# Multi-tenant / Multiple Paybills & Tills

This package supports **multiple Mpesa configurations** ("profiles"). This is useful in SaaS systems where each customer has their own:

- Consumer key / secret
- Paybill / Till shortcode
- STK callback URL
- Validation / confirmation URLs

## Config: `mpesa.profiles`

Define profiles in `config/mpesa.php`:

```php
return [
    'default_profile' => env('MPESA_DEFAULT_PROFILE', 'default'),

    'profiles' => [
        'default' => [
            'consumerKey' => env('MPESA_CONSUMER_KEY'),
            'consumerSecret' => env('MPESA_CONSUMER_SECRET'),
            'lipaNaMpesaShortcode' => env('LIPA_NA_MPESA_SHORTCODE'),
            'lipaNaMpesaPasskey' => env('LIPA_NA_MPESA_PASSKEY'),
            'lipaNaMpesaCallbackURL' => env('LIPA_NA_MPESA_CALLBACK_URL'),
            // ...other keys...
        ],

        'tenant_a' => [
            'consumerKey' => env('TENANT_A_MPESA_CONSUMER_KEY'),
            'consumerSecret' => env('TENANT_A_MPESA_CONSUMER_SECRET'),
            'lipaNaMpesaShortcode' => env('TENANT_A_LIPA_NA_MPESA_SHORTCODE'),
            'lipaNaMpesaPasskey' => env('TENANT_A_LIPA_NA_MPESA_PASSKEY'),
            'lipaNaMpesaCallbackURL' => env('TENANT_A_LIPA_NA_MPESA_CALLBACK_URL'),
        ],
    ],
];
```

## Usage

### Using the Facade

```php
use Itsmurumba\Mpesa\Facades\Mpesa;

Mpesa::for('tenant_a')->expressPayment($amount, $phoneNumber);
```

### Using the container

```php
$mpesa = app('mpesa')->for('tenant_a');
$mpesa->expressPayment($amount, $phoneNumber);
```

### Backwards compatibility (single config)

If you don’t need multiple profiles, you can continue using the root keys in `config/mpesa.php`:

```php
$mpesa = new \Itsmurumba\Mpesa\Mpesa();
$mpesa->expressPayment($amount, $phoneNumber);
```

