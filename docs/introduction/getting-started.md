# Getting Started with Laravel M-Pesa Package

## Overview

The Laravel M-Pesa package provides a seamless integration with Safaricom's M-Pesa Daraja API, enabling you to implement various M-Pesa payment solutions in your Laravel applications. This package supports all major M-Pesa APIs including payments, disbursements, account management, and more.

This guide will walk you through the complete setup process, from installation to implementing your first M-Pesa payment, with practical examples and best practices.

## Key Features

- **Complete M-Pesa API Coverage**: Support for all major M-Pesa APIs
- **Easy Integration**: Simple setup and configuration
- **Secure Transactions**: Built-in security credential encryption
- **Real-time Processing**: Instant payment processing and confirmation
- **Callback Handling**: Comprehensive callback management
- **Error Handling**: Robust error handling and logging
- **Testing Support**: Built-in testing utilities
- **Documentation**: Comprehensive documentation for all APIs

## Supported APIs

### Payment APIs
- **Express Payment (STK Push)**: Customer-initiated payments
- **C2B (Customer to Business)**: Customer payments to business
- **B2C (Business to Customer)**: Business payments to customers
- **B2B (Business to Business)**: Business-to-business payments
- **Business Pay Bill**: Paybill payments
- **Business Buy Goods**: Till number payments
- **B2B Express Checkout**: USSD Push to Till
- **Dynamic QR Code**: QR code generation for payments
- **Tax Remittance**: KRA tax payments
- **M-Pesa Ratiba (Standing Orders)**: Recurring payments

### Disbursement APIs
- **B2C Disbursements**: Bulk payments to customers


### Experience APIs
- **Account Balance**: Check account balances
- **Bill Manager**: Invoice and bill management
- **Account Balance**: Balance inquiries
- **Transaction Status**: Query transaction status
- **Reversals**: Reverse failed transactions

## Prerequisites

Before you begin, ensure you have:

- **Laravel Application**: A Laravel application (see [composer.json](https://github.com/ItsMurumba/laravel-mpesa/blob/main/composer.json) for supported versions; Laravel 8+ recommended)
- **Composer**: PHP package manager installed
- **M-Pesa Daraja Account**: Active Safaricom Daraja API account
- **API Credentials**: Consumer key, consumer secret, and other required credentials
- **Public Domain**: Accessible callback URLs for webhook handling
- **SSL Certificate**: HTTPS enabled for production callbacks

### Required M-Pesa Credentials

You'll need the following credentials from your Safaricom Daraja account:

- **Consumer Key**: Your application's consumer key
- **Consumer Secret**: Your application's consumer secret
- **Shortcode**: Your business shortcode (for STK Push)
- **Passkey**: Your STK Push passkey
- **Initiator Username**: For B2C/B2B transactions
- **Initiator Password**: Encrypted password for B2C/B2B transactions

## Installation

### 1. Install via Composer

```bash
composer require itsmurumba/laravel-mpesa
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --provider="Itsmurumba\Mpesa\MpesaServiceProvider"
```

### 3. Get M-Pesa Credentials

To get your M-Pesa API credentials:

1. **Visit Safaricom Daraja Portal**: Go to [https://developer.safaricom.co.ke/](https://developer.safaricom.co.ke/)
2. **Create Account**: Sign up for a Daraja developer account
3. **Create App**: Create a new application in the portal
4. **Get Credentials**: Copy your consumer key and consumer secret
5. **Request Shortcode**: Apply for a business shortcode if needed
6. **Generate Passkey**: Generate your STK Push passkey

### 4. Configure Environment Variables

Add the following variables to your `.env` file:

```env
# M-Pesa Environment
MPESA_ENVIRONMENT=sandbox

# API Credentials
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret

# Business Details (env keys match config/mpesa.php)
LIPA_NA_MPESA_SHORTCODE=your_shortcode
LIPA_NA_MPESA_PASSKEY=your_passkey

# B2C/B2B Credentials
MPESA_INITIATOR_USERNAME=your_initiator_username
MPESA_INITIATOR_PASSWORD=your_initiator_password

# Callback URLs
MPESA_RESULT_URL=https://your-domain.com/mpesa/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/timeout
LIPA_NA_MPESA_CALLBACK_URL=https://your-domain.com/mpesa/stk/callback
MPESA_CONFIRMATION_URL=https://your-domain.com/mpesa/c2b/confirmation
MPESA_VALIDATION_URL=https://your-domain.com/mpesa/c2b/validation
```

## Basic Configuration

### Configuration File

The package configuration is located at `config/mpesa.php`. It supports a single default profile (root keys) and [multiple profiles (multi-tenant)](/introduction/multi-tenant) via the `profiles` array or database.

**Single profile (root keys):**

```php
return [
    'default_profile' => env('MPESA_DEFAULT_PROFILE', 'default'),
    'use_database' => env('MPESA_USE_DATABASE', false),

    'consumerKey' => env('MPESA_CONSUMER_KEY', ''),
    'consumerSecret' => env('MPESA_CONSUMER_SECRET', ''),
    'baseUrl' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),
    'paybillNumber' => env('MPESA_PAYBILL_NUMBER'),
    'lipaNaMpesaShortcode' => env('LIPA_NA_MPESA_SHORTCODE'),
    'lipaNaMpesaPasskey' => env('LIPA_NA_MPESA_PASSKEY'),
    'lipaNaMpesaCallbackURL' => env('LIPA_NA_MPESA_CALLBACK_URL'),
    'callBackURL' => env('MPESA_CALLBACK_URL'),
    'confirmationURL' => env('MPESA_CONFIRMATION_URL'),
    'validationURL' => env('MPESA_VALIDATION_URL'),
    'initiatorUsername' => env('MPESA_INITIATOR_USERNAME'),
    'initiatorPassword' => env('MPESA_INITIATOR_PASSWORD'),
    'queueTimeOutURL' => env('MPESA_QUEUE_TIMEOUT_URL'),
    'resultURL' => env('MPESA_RESULT_URL'),

    'profiles' => [
        'default' => [
            'consumerKey' => env('MPESA_CONSUMER_KEY'),
            'consumerSecret' => env('MPESA_CONSUMER_SECRET'),
            'lipaNaMpesaShortcode' => env('LIPA_NA_MPESA_SHORTCODE'),
            'lipaNaMpesaPasskey' => env('LIPA_NA_MPESA_PASSKEY'),
            // ... other keys
        ],
    ],
];
```

For multiple paybills/tills (SaaS), see [Multi-tenant / Profiles](/introduction/multi-tenant).

## Testing Your Installation

### Verify Configuration

API methods return a **JSON string**; decode it before reading keys. Example route to verify config and auth:

```php
// routes/web.php
Route::get('/test-mpesa', function () {
    try {
        $mpesa = new \Itsmurumba\Mpesa\Mpesa();

        // Test auth by initiating an STK push (sandbox will respond even with test data)
        $response = $mpesa->expressPayment(1, '254708374149', 'TEST', 'Test');
        $data = json_decode($response, true);

        if (is_array($data) && isset($data['ResponseCode'])) {
            return response()->json([
                'status' => 'success',
                'message' => 'M-Pesa configuration is working',
                'response' => $data,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Unexpected response',
            'raw' => $response,
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'M-Pesa configuration error: ' . $e->getMessage(),
        ], 500);
    }
});
```

### Check Environment

```bash
# Verify your environment variables are loaded
php artisan tinker
>>> env('MPESA_ENVIRONMENT')
>>> env('MPESA_CONSUMER_KEY')
```

## Quick Start Examples

All API methods return a **JSON string**. Use `json_decode($response, true)` to get an array and check `ResponseCode`, `CheckoutRequestID`, etc.

### 1. Express Payment (STK Push)

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

$response = $mpesa->expressPayment(
    amount: 1000,
    phoneNumber: '254700000000',
    accountReference: 'INV-001',
    transactionDescription: 'Payment for services'
);
$data = json_decode($response, true);
```

### 2. C2B Payment

```php
$response = $mpesa->c2bPayment(
    commandId: 'CustomerPayBillOnline',
    amount: 500,
    phoneNumber: '254700000000',
    billRefNumber: 'BILL-001'
);
```

### 3. B2C Payment

```php
$response = $mpesa->b2cPayment(
    commandId: 'BusinessPayment',
    amount: 1000,
    phoneNumber: '254700000000',
    remarks: 'Salary payment'
);
```

### 4. B2B Payment

```php
$response = $mpesa->b2bPayment(
    commandId: 'BusinessPayBill',
    amount: 5000,
    receiverShortcode: '123456',
    accountReference: 'BIZ-ACC-001',
    remarks: 'Business payment'
);
```

### 5. Dynamic QR Code

```php
$response = $mpesa->generateDynamicQRCode(
    merchantName: 'My Business',
    transactionReference: 'TXN-001',
    amount: 1000,
    transactionType: 'PB',
    creditPartyIdentifier: '123456'
);
```

### 6. Standing Order (Ratiba)

```php
$response = $mpesa->createRatibaStandingOrder(
    orderName: 'Monthly Rent',
    startDate: '20240101',
    endDate: '20241231',
    businessShortCode: '123456',
    amount: 50000,
    phoneNumber: '254700000000',
    callBackURL: 'https://your-domain.com/callback',
    accountReference: 'RENT-001',
    transactionDesc: 'Monthly rent',
    frequency: '4',
    transactionType: 'Standing Order Customer Pay Bill',
    receiverPartyIdentifierType: '4'
);
```

## Error Handling

### Basic Error Handling

```php
use Itsmurumba\Mpesa\Mpesa;
use Itsmurumba\Mpesa\Exceptions\IsNullException;
use Exception;

try {
    $mpesa = new Mpesa();
    
    $response = $mpesa->expressPayment(
        amount: 1000,
        phoneNumber: '254700000000',
        accountReference: 'INV-001',
        transactionDescription: 'Payment for services'
    );
    
    // Handle successful response
    $data = json_decode($response, true);
    if (is_array($data) && isset($data['ResponseCode']) && $data['ResponseCode'] === '0') {
        // Payment initiated successfully
        Log::info('Payment initiated', $data);
    }
    
} catch (IsNullException $e) {
    // Handle missing configuration
    Log::error('M-Pesa configuration missing: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other errors
    Log::error('M-Pesa payment error: ' . $e->getMessage());
}
```

## Callback Handling

### Express Payment Callback

```php
// routes/web.php
Route::post('/mpesa/stk/callback', function (Request $request) {
    $data = $request->all();
    
    if (isset($data['Body']['stkCallback'])) {
        $callback = $data['Body']['stkCallback'];
        
        if ($callback['ResultCode'] === 0) {
            // Payment successful
            $transactionId = $callback['CallbackMetadata']['Item'][1]['Value'];
            $amount = $callback['CallbackMetadata']['Item'][0]['Value'];
            
            // Update your database
            Payment::where('checkout_request_id', $callback['CheckoutRequestID'])
                ->update([
                    'status' => 'completed',
                    'transaction_id' => $transactionId,
                    'completed_at' => now()
                ]);
        } else {
            // Payment failed
            Log::error('STK Push failed', $callback);
        }
    }
    
    return response()->json(['status' => 'received']);
});
```

### C2B Callback

```php
Route::post('/mpesa/c2b/confirmation', function (Request $request) {
    $data = $request->all();
    
    // Handle C2B confirmation
    if (isset($data['TransID'])) {
        C2BPayment::create([
            'transaction_id' => $data['TransID'],
            'amount' => $data['TransAmount'],
            'phone_number' => $data['MSISDN'],
            'bill_reference' => $data['BillRefNumber'],
            'business_shortcode' => $data['BusinessShortCode'],
            'invoice_number' => $data['InvoiceNumber'],
            'org_account_balance' => $data['OrgAccountBalance'],
            'third_party_trans_id' => $data['ThirdPartyTransID'],
            'first_name' => $data['FirstName'],
            'middle_name' => $data['MiddleName'],
            'last_name' => $data['LastName'],
            'transaction_time' => $data['TransTime'],
        ]);
    }
    
    return response()->json(['status' => 'received']);
});
```

## Testing

### Unit Testing

```php
// tests/Unit/MpesaTest.php
public function test_express_payment()
{
    $mpesa = new Mpesa();

    $response = $mpesa->expressPayment(
        amount: 1000,
        phoneNumber: '254700000000',
        accountReference: 'TEST-001',
        transactionDescription: 'Test payment'
    );
    $data = json_decode($response, true);

    $this->assertIsArray($data);
    $this->assertArrayHasKey('CheckoutRequestID', $data);
    $this->assertArrayHasKey('ResponseCode', $data);
}
```

### Integration Testing

```php
// tests/Feature/MpesaIntegrationTest.php
public function test_payment_workflow()
{
    $mpesa = new Mpesa();

    $response = $mpesa->expressPayment(
        amount: 1000,
        phoneNumber: '254700000000',
        accountReference: 'TEST-001',
        transactionDescription: 'Test payment'
    );
    $data = json_decode($response, true);
    $this->assertIsArray($data);

    if (isset($data['ResponseCode']) && $data['ResponseCode'] === '0' && !empty($data['CheckoutRequestID'])) {
        $statusResponse = $mpesa->expressPaymentQuery($data['CheckoutRequestID']);
        $statusData = json_decode($statusResponse, true);
        $this->assertArrayHasKey('ResponseCode', $statusData);
    }
}
```

## Security Best Practices

### 1. Environment Configuration

- Always use environment variables for sensitive data
- Never commit API credentials to version control
- Use different credentials for sandbox and production

### 2. Input Validation

```php
public function validatePaymentRequest($amount, $phoneNumber, $accountReference)
{
    if ($amount <= 0 || $amount > 10000000) {
        throw new InvalidArgumentException('Invalid amount');
    }
    
    if (!preg_match('/^254[0-9]{9}$/', $phoneNumber)) {
        throw new InvalidArgumentException('Invalid phone number format');
    }
    
    if (strlen($accountReference) > 20) {
        throw new InvalidArgumentException('Account reference too long');
    }
    
    return true;
}
```

### 3. Rate Limiting

```php
use Illuminate\Cache\RateLimiter;

public function processPaymentWithRateLimit($amount, $phoneNumber, $accountReference)
{
    $rateLimiter = app(RateLimiter::class);
    $key = "mpesa_payment:{$phoneNumber}";
    
    if ($rateLimiter->tooManyAttempts($key, 10)) {
        throw new Exception('Too many payment attempts');
    }
    
    $rateLimiter->hit($key, 3600);
    
    return $this->mpesa->expressPayment($amount, $phoneNumber, $accountReference);
}
```

## Common Setup Issues

### 1. Package Not Found

If you get a "Package not found" error:

```bash
# Clear composer cache
composer clear-cache

# Update composer
composer update

# Try installing again
composer require itsmurumba/laravel-mpesa
```

### 2. Configuration Not Published

If the configuration file is not published:

```bash
# Publish the configuration manually
php artisan vendor:publish --provider="Itsmurumba\Mpesa\MpesaServiceProvider" --force
```

### 3. Environment Variables Not Loading

If your environment variables are not being loaded:

```bash
# Clear configuration cache
php artisan config:clear
php artisan cache:clear

# Restart your web server
sudo service apache2 restart  # or nginx
```

## Troubleshooting

### Common Issues

1. **Configuration Errors**
   - Check all environment variables are set
   - Verify API credentials are correct
   - Ensure callback URLs are accessible

2. **Authentication Errors**
   - Verify consumer key and secret
   - Check initiator username and password
   - Ensure security credentials are properly encrypted

3. **Callback Issues**
   - Verify callback URLs are publicly accessible
   - Check SSL certificates for HTTPS URLs
   - Ensure callback handling logic is correct

4. **Transaction Failures**
   - Check account balances
   - Verify shortcode is active
   - Ensure phone numbers are in correct format

### Debug Mode

```php
// Enable debug logging
Log::channel('mpesa')->info('M-Pesa request', [
    'amount' => $amount,
    'phone_number' => $phoneNumber,
    'account_reference' => $accountReference,
    'timestamp' => now()
]);
```

## Production Deployment

### Environment Setup

When deploying to production:

1. **Update Environment**: Change `MPESA_ENVIRONMENT` to `production`
2. **Update Base URL**: Use production M-Pesa API endpoints
3. **Secure Credentials**: Ensure all credentials are properly secured
4. **SSL Certificates**: Verify HTTPS is enabled for all callback URLs
5. **Error Monitoring**: Set up proper error logging and monitoring

### Production Checklist

- [ ] All environment variables are set correctly
- [ ] Callback URLs are publicly accessible
- [ ] SSL certificates are valid
- [ ] Error handling is implemented
- [ ] Logging is configured
- [ ] Rate limiting is in place
- [ ] Security measures are implemented

## Next Steps

- [Multi-tenant / multiple paybills](/introduction/multi-tenant) — Use `Mpesa::for('profile')->expressPayment(...)` for multiple tenants.
- Explore the sidebar for each API: M-Pesa Express, C2B, B2C, B2B, Dynamic QR, Ratiba, and more.

## Support

- **Package Repository**: [GitHub Repository](https://github.com/ItsMurumba/laravel-mpesa)
- **Documentation**: [Documentation](/)
- **Issues**: [GitHub Issues](https://github.com/ItsMurumba/laravel-mpesa/issues)
- **Contributing**: [Contribution Guide](https://github.com/ItsMurumba/laravel-mpesa/blob/main/Contribution.md)

## License

This package is open-sourced software licensed under the [MIT license](https://github.com/ItsMurumba/laravel-mpesa/blob/main/LICENSE).