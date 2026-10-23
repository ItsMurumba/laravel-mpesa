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
- Root keys in `config/mpesa.php` act as defaults for missing fields, including database columns left empty
- A database error (for example, the connection is down) is thrown, never treated as "not found", so a request can't go out with another profile's credentials
- A profile name that isn't in the database or config throws `MpesaException`, unless the call passes its own overrides (`Mpesa::for('tenant-id', [...])`)
- A profile that sets its own initiator without a `securityCredential` doesn't inherit the root one; its `initiatorPassword` is encrypted instead

#### Storing secrets

Encrypt secrets before storing them. The package decrypts `consumer_secret`, `lipa_na_mpesa_passkey`, `initiator_password` and `security_credential` with Laravel’s `Crypt::decryptString()`, and uses the raw value if one isn’t encrypted:

```php
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encryptString($plainPasskey);
```

**Step 3:** Store tenant profiles in the `mpesa_profiles` table. Columns (from the migration) include:

| Column | Type | Required | Description |
|--------|------|----------|-------------|
| `name` | string | Yes | Profile identifier (e.g. `tenant_a`); must be unique |
| `consumer_key` | string | Yes | Daraja consumer key |
| `consumer_secret` | text | Yes | Daraja consumer secret (can be encrypted) |
| `base_url` | string | No | Default `https://sandbox.safaricom.co.ke` |
| `paybill_number` | string | No | Paybill number |
| `lipa_na_mpesa_shortcode` | string | No | STK Push shortcode |
| `lipa_na_mpesa_passkey` | text | No | STK Push passkey (can be encrypted) |
| `lipa_na_mpesa_callback_url` | string | No | STK callback URL |
| `lipa_na_mpesa_transaction_type` | string | No | `CustomerPayBillOnline` or `CustomerBuyGoodsOnline` (Buy Goods tills) |
| `lipa_na_mpesa_till_number` | string | No | Till sent as `PartyB` when the passkey belongs to another shortcode |
| `callback_url` | string | No | Generic callback URL |
| `confirmation_url` | string | No | C2B confirmation URL |
| `validation_url` | string | No | C2B validation URL |
| `initiator_username` | string | No | B2C/B2B initiator username |
| `initiator_password` | text | No | B2C/B2B initiator password (can be encrypted) |
| `security_credential` | text | No | Pre-encrypted security credential from the Daraja portal (can be encrypted) |
| `environment` | string | No | `sandbox` or `production` |
| `queue_timeout_url` | string | No | Queue timeout callback URL |
| `result_url` | string | No | Result callback URL |
| `is_active` | boolean | No | Default `true`; inactive profiles are ignored |

Example insert:

```php
DB::table('mpesa_profiles')->insert([
    'name' => 'tenant_a',
    'consumer_key' => 'key-a',
    'consumer_secret' => Crypt::encryptString('secret-a'),
    'lipa_na_mpesa_shortcode' => '111111',
    'lipa_na_mpesa_passkey' => Crypt::encryptString('passkey-a'),
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

