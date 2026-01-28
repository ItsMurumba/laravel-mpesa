# Multi-tenant / Multiple Paybills & Tills

This package supports **multiple Mpesa configurations** ("profiles"). This is useful in SaaS systems where each customer has their own:

- Consumer key / secret
- Paybill / Till shortcode
- STK callback URL
- Validation / confirmation URLs

## Two Approaches

### 1. Database-driven (Recommended for SaaS)

For dynamic tenants that are added/removed at runtime, use the database approach:

**Step 1:** Publish and run the migration:

```bash
php artisan vendor:publish --tag=mpesa-migrations
php artisan migrate
```

**Step 2:** Enable database lookups in `config/mpesa.php`:

```php
return [
    'use_database' => env('MPESA_USE_DATABASE', true),
    // ... rest of config
];
```

**Step 3:** Store tenant profiles in the `mpesa_profiles` table:

```php
DB::table('mpesa_profiles')->insert([
    'name' => 'tenant_a',
    'consumer_key' => 'key-a',
    'consumer_secret' => 'secret-a',
    'lipa_na_mpesa_shortcode' => '111111',
    'lipa_na_mpesa_passkey' => 'passkey-a',
    'environment' => 'sandbox',
    'is_active' => true,
    // ... other fields
]);
```

**Step 4:** Use profiles:

```php
Mpesa::for('tenant_a')->expressPayment($amount, $phoneNumber);
```

### 2. Config-based (Static profiles)

For a small, fixed number of tenants, define profiles in `config/mpesa.php`:

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

