# Upgrading to 2.0

2.0 sends every request through Laravel's HTTP client and fetches the access token on the first API call. Most apps only need to bump the version and check how they handle errors.

```bash
composer require itsmurumba/laravel-mpesa:^2.0
```

## Requirements

- PHP 8.2 or later
- Laravel 11, 12 or 13

On an older version, stay on 1.x (`^1.0`).

## Daraja errors are returned, not thrown

In 1.x, a 4xx or 5xx response from Daraja threw a Guzzle `ClientException` or `ServerException`. In 2.0 the error body is returned as a string, the same way a success is. Check the response instead of catching Guzzle exceptions:

```php
$response = json_decode(Mpesa::expressPayment($amount, $phone, $reference, $description));

if (isset($response->errorCode)) {
    // Daraja rejected the request: $response->errorMessage
} elseif ($response->ResponseCode !== '0') {
    // Accepted by the API but not processed: $response->ResponseDescription
}
```

## Access token failures throw `MpesaException`

The constructor no longer calls Safaricom. The token is fetched on the first API call, and if it can't be obtained (wrong consumer key or secret, a non-2xx response, or no connection), that call throws `Itsmurumba\Mpesa\Exceptions\MpesaException` and nothing is sent. In 1.x the request went out with an empty bearer token.

```php
use Itsmurumba\Mpesa\Exceptions\MpesaException;

try {
    $response = Mpesa::expressPayment($amount, $phone, $reference, $description);
} catch (MpesaException $e) {
    // Credentials are wrong or Daraja is unreachable
}
```

Credential errors now show up on the first API call, not when the `Mpesa` instance is created.

## Testing with `Http::fake()`

Because requests go through Laravel's HTTP client, you can fake Safaricom in your tests:

```php
Http::fake([
    '*/oauth/v1/generate*' => Http::response(['access_token' => 'token', 'expires_in' => '3599']),
    '*/mpesa/stkpush/v1/processrequest' => Http::response(['ResponseCode' => '0']),
]);
```

## Subclasses

If you extend `Mpesa`, note that the protected `$client` property and the private `setRequestOptions()` method are gone, and `$expiresIn` is now a Unix timestamp.

## New in 2.0

- Buy Goods tills for M-Pesa Express: `lipaNaMpesaTransactionType` and `lipaNaMpesaTillNumber`. See [M-Pesa Express](/payments/mpesa-express).
- A pre-encrypted `securityCredential` can be used in place of encrypting `initiatorPassword`. See [Authorization](/security/authorization).
- Multiple M-Pesa profiles, from config or the database, and per-call overrides through `Mpesa::for()`. See [Multi-tenant / Profiles](/introduction/multi-tenant).
- Laravel 13 support.
