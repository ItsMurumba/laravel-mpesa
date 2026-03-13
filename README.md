# Laravel M-Pesa

Laravel package for the Safaricom M-Pesa Daraja API. Supports C2B, B2C, B2B, M-Pesa Express (STK Push), transaction status, account balance, reversals, dynamic QR, tax remittance, M-Pesa Ratiba, and more.

**[Full documentation →](https://mpesa.itsmurumba.dev)**

## Requirements

- PHP 7.1+
- Laravel 5.0+ (Laravel 8+ recommended)
- [Composer](https://getcomposer.org/)

## Installation

```bash
composer require itsmurumba/laravel-mpesa
```

Publish the config file:

```bash
php artisan vendor:publish --provider="Itsmurumba\Mpesa\MpesaServiceProvider" --tag=mpesa-config
```

For Laravel 5.4 and below, register the service provider in `config/app.php`:

```php
Itsmurumba\Mpesa\MpesaServiceProvider::class
```

## Configuration

Add your M-Pesa credentials to `.env`. See the [configuration guide](https://mpesa.itsmurumba.dev/introduction/getting-started#basic-configuration) for all options.

```env
MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
LIPA_NA_MPESA_SHORTCODE=
LIPA_NA_MPESA_PASSKEY=
LIPA_NA_MPESA_CALLBACK_URL=
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_ENVIRONMENT=sandbox
# ... (see docs for full list)
```

## Usage

**Single paybill/till:**

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();
$mpesa->expressPayment($amount, $phoneNumber);
```

**Multiple paybills (multi-tenant / SaaS):**

```php
use Itsmurumba\Mpesa\Facades\Mpesa;

Mpesa::for('tenant-id')->expressPayment($amount, $phoneNumber);
```

Profiles can be defined in config or stored in the database. [Multi-tenant setup →](https://mpesa.itsmurumba.dev/introduction/multi-tenant)

## Supported APIs

M-Pesa Express, C2B, B2C, B2B, Paybill, Buy Goods, B2B Express Checkout, Dynamic QR, Tax Remittance, M-Pesa Ratiba, Account Balance, Transaction Status, Reversals, Bill Manager. Method signatures and examples are in the [documentation](https://mpesa.itsmurumba.dev).

## Docs (GitHub Pages)

The documentation in `docs/` is built with [VitePress](https://vitepress.dev) and deployed automatically via GitHub Actions.

**One-time setup:** In the repo go to **Settings → Pages**. Under "Build and deployment", set **Source** to **GitHub Actions**.

After that, every push to `main` that changes files under `docs/` will build and deploy the site. The workflow file is [`.github/workflows/deploy-docs.yml`](.github/workflows/deploy-docs.yml). If you use a custom domain for Pages, set `base: '/'` in `docs/.vitepress/config.mjs` (it is currently `'/laravel-mpesa/'` for the default `https://<owner>.github.io/laravel-mpesa/` URL).

## Contributing

Contributions are welcome. Please read [Contribution.md](Contribution.md) before submitting PRs or issues.

## License

MIT. See [LICENSE](LICENSE) for details.

---

Give the repo a star to support the project. Follow [@ItsMurumba](https://twitter.com/ItsMurumba) on Twitter.
