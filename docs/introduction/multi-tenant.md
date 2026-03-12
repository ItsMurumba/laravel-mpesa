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

#### Precedence (DB vs config)

When `use_database` is enabled:

- If a profile exists in `mpesa_profiles` and is active, **DB values take precedence**
- If not found (or table missing), the package falls back to **config-based profiles**
- Root keys in `config/mpesa.php` act as defaults for missing fields

#### Storing secrets

If you store secrets in the database, it’s recommended to encrypt them at rest. The package will attempt to decrypt `initiator_password` using Laravel’s `Crypt::decryptString()` and falls back to the raw value if it isn’t encrypted. Encrypt before storing:

```php
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString($plainInitiatorPassword);
```

**Step 3:** Store tenant profiles in the `mpesa_profiles` table. Columns (from the migration) include:

| Column | Type | Required | Description |
|--------|------|----------|-------------|
| `name` | string | Yes | Profile identifier (e.g. `tenant_a`); must be unique |
| `consumer_key` | string | Yes | Daraja consumer key |
| `consumer_secret` | string | Yes | Daraja consumer secret |
| `base_url` | string | No | Default `https://sandbox.safaricom.co.ke` |
| `paybill_number` | string | No | Paybill number |
| `lipa_na_mpesa_shortcode` | string | No | STK Push shortcode |
| `lipa_na_mpesa_passkey` | string | No | STK Push passkey |
| `lipa_na_mpesa_callback_url` | string | No | STK callback URL |
| `callback_url` | string | No | Generic callback URL |
| `confirmation_url` | string | No | C2B confirmation URL |
| `validation_url` | string | No | C2B validation URL |
| `initiator_username` | string | No | B2C/B2B initiator username |
| `initiator_password` | text | No | B2C/B2B initiator password (can be encrypted) |
| `environment` | string | No | `sandbox` or `production` |
| `queue_timeout_url` | string | No | Queue timeout callback URL |
| `result_url` | string | No | Result callback URL |
| `is_active` | boolean | No | Default `true`; inactive profiles are ignored |

Example insert:

```php
DB::table('mpesa_profiles')->insert([
    'name' => 'tenant_a',
    'consumer_key' => 'key-a',
    'consumer_secret' => 'secret-a',
    'lipa_na_mpesa_shortcode' => '111111',
    'lipa_na_mpesa_passkey' => 'passkey-a',
    'environment' => 'sandbox',
    'is_active' => true,
    // ... other fields as needed
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

