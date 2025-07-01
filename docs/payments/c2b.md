# C2B (Customer to Business) Payments

C2B (Customer to Business) payments allow customers to initiate payments directly to your business through M-Pesa. This service enables customers to pay for goods and services by sending money to your paybill or till number.

## Overview

C2B payments enable businesses to:
- Receive payments directly from customers via M-Pesa
- Register callback URLs for payment notifications
- Simulate payments for testing purposes
- Process payments without initiating the transaction themselves

**Good News**: The Laravel M-Pesa package makes C2B implementation simple and straightforward!

## How C2B Works in This Package

### Two-Step Process

The package handles C2B payments in two main steps:

1. **URL Registration**: Register validation and confirmation URLs with M-Pesa
2. **Payment Simulation**: Simulate customer payments for testing (or receive real customer payments)

### Automatic Configuration

The package automatically handles all the complex configuration:

1. **Business Shortcode**: Uses your configured Lipa na M-Pesa shortcode
2. **Callback URLs**: Uses your configured confirmation and validation URLs
3. **Response Type**: Automatically sets the correct response type
4. **Authorization**: Automatically handles authentication tokens

### Implementation Details

```php
// Simple usage - the package handles all the complexity
$mpesa = new Mpesa();

// Register C2B URLs
$response = $mpesa->c2bRegisterURLs();

// Simulate a C2B payment
$payment = $mpesa->c2bPayment('CustomerPayBillOnline', 100, '254700000000', 'INV001');
```

## Configuration

### Required Configuration

Make sure your `config/mpesa.php` file contains the necessary C2B settings:

```php
return [
    'lipaNaMpesaShortcode' => env('MPESA_LIPA_NA_MPESA_SHORTCODE'),
    'confirmationURL' => env('MPESA_CONFIRMATION_URL'),
    'validationURL' => env('MPESA_VALIDATION_URL'),
    // ... other configuration
];
```

### Environment Variables

Add these to your `.env` file:

```env
MPESA_LIPA_NA_MPESA_SHORTCODE=174379
MPESA_CONFIRMATION_URL=https://your-domain.com/mpesa/c2b/confirmation
MPESA_VALIDATION_URL=https://your-domain.com/mpesa/c2b/validation
```

## API Endpoints

### 1. Register C2B URLs

**Endpoint:** `POST /mpesa/c2b/v1/registerurl`

### 2. Simulate C2B Payment

**Endpoint:** `POST /mpesa/c2b/v1/simulate`

## Implementation

### Registering C2B URLs

Before you can receive C2B payments, you must register your callback URLs with M-Pesa:

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

// Register C2B URLs - this only needs to be done once
$response = $mpesa->c2bRegisterURLs();
```

### Behind the Scenes

When you call `c2bRegisterURLs()`, the package automatically:

1. **Uses Shortcode**: Gets your configured business shortcode
2. **Sets Response Type**: Uses 'Completed' as the response type
3. **Uses Callback URLs**: Gets your configured confirmation and validation URLs
4. **Sends Request**: Makes the API call with proper authentication
5. **Returns Response**: Returns the M-Pesa response

### URL Registration Payload (Automatic)

```php
// This is handled automatically by the package
$arrayData = [
    'ShortCode' => $this->lipaNaMpesaShortcode,
    'ResponseType' => 'Completed',
    'ConfirmationURL' => $this->confirmationURL,
    'ValidationURL' => $this->validationURL,
];
```

### Simulating C2B Payments

For testing purposes, you can simulate C2B payments:

```php
// Simulate a C2B payment
$response = $mpesa->c2bPayment(
    commandId: 'CustomerPayBillOnline',    // Command ID
    amount: 100,                           // Amount in KES
    phoneNumber: '254700000000',           // Customer's phone number
    billRefNumber: 'INV001'                // Bill reference number
);
```

### Available Command IDs

| Command ID | Description | Use Case |
|------------|-------------|----------|
| `CustomerPayBillOnline` | Pay bill online | Standard bill payments |
| `CustomerBuyGoodsOnline` | Buy goods online | E-commerce transactions |

### Payment Simulation Payload (Automatic)

```php
// This is handled automatically by the package
$arrayData = [
    'ShortCode' => $this->lipaNaMpesaShortcode,
    'CommandID' => $commandId,
    'amount' => $amount,
    'MSISDN' => $phoneNumber,
    'BillRefNumber' => $billRefNumber,
];
```

## Response Format

### URL Registration Response

**Success Response:**

```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "0",
    "ResponseDescription": "success"
}
```

### Payment Simulation Response

**Success Response:**

```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully.",
    "TransactionID": "QK123456789"
}
```

## Response Parameters

### URL Registration Response

| Parameter | Description | Type | Sample Values |
|-----------|-------------|------|---------------|
| `ConversationID` | Unique conversation identifier | String | `AG_20240115_123456789` |
| `OriginatorConversationID` | Original conversation ID | String | `123456789` |
| `ResponseCode` | Response code from M-Pesa | String | `0` |
| `ResponseDescription` | Description of the response | String | `success` |

### Payment Simulation Response

| Parameter | Description | Type | Sample Values |
|-----------|-------------|------|---------------|
| `ConversationID` | Unique conversation identifier | String | `AG_20240115_123456789` |
| `OriginatorConversationID` | Original conversation ID | String | `123456789` |
| `ResponseCode` | Response code from M-Pesa | String | `0` |
| `ResponseDescription` | Description of the response | String | `Accept the service request successfully.` |
| `TransactionID` | M-Pesa transaction ID | String | `QK123456789` |

## Error Responses

### Common Error Codes

**Invalid Shortcode:**
```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid ShortCode"
}
```

**Invalid Phone Number:**
```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid MSISDN"
}
```

**URLs Already Registered:**
```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - URLs already registered"
}
```

## Testing

### Sandbox Testing

For testing purposes, use the sandbox environment:

```php
// Use sandbox credentials
MPESA_LIPA_NA_MPESA_SHORTCODE=174379
MPESA_CONFIRMATION_URL=https://your-domain.com/mpesa/c2b/confirmation
MPESA_VALIDATION_URL=https://your-domain.com/mpesa/c2b/validation
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
```

### Test Phone Numbers

Use these test phone numbers for sandbox testing:
- `254708374149` - Success scenario
- `254708374150` - Insufficient funds
- `254708374151` - User cancelled

### Complete Testing Example

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

try {
    // Step 1: Register C2B URLs (only needed once)
    $registrationResponse = $mpesa->c2bRegisterURLs();
    $registrationData = json_decode($registrationResponse, true);
    
    if ($registrationData['ResponseCode'] === '0') {
        echo "C2B URLs registered successfully\n";
        
        // Step 2: Simulate a C2B payment
        $paymentResponse = $mpesa->c2bPayment(
            'CustomerPayBillOnline',
            100,
            '254708374149',
            'TEST_INV_001'
        );
        
        $paymentData = json_decode($paymentResponse, true);
        
        if ($paymentData['ResponseCode'] === '0') {
            echo "Payment simulated successfully\n";
            echo "Transaction ID: " . $paymentData['TransactionID'] . "\n";
        } else {
            echo "Payment simulation failed: " . $paymentData['ResponseDescription'] . "\n";
        }
    } else {
        echo "URL registration failed: " . $registrationData['ResponseDescription'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Callback Handling

### Setting Up Callbacks

Configure your callback URLs in your environment:

```env
MPESA_CONFIRMATION_URL=https://your-domain.com/mpesa/c2b/confirmation
MPESA_VALIDATION_URL=https://your-domain.com/mpesa/c2b/validation
```

### Confirmation Callback

When a customer makes a payment, M-Pesa sends a confirmation to your confirmation URL:

```json
{
    "TransactionType": "Pay Bill",
    "TransID": "QK123456789",
    "TransTime": "20240115123456",
    "TransAmount": "100.00",
    "BusinessShortCode": "174379",
    "BillRefNumber": "INV001",
    "InvoiceNumber": "",
    "OrgAccountBalance": "1000.00",
    "ThirdPartyTransID": "",
    "MSISDN": "254700000000",
    "FirstName": "John",
    "MiddleName": "",
    "LastName": "Doe"
}
```

### Validation Callback

M-Pesa may also send validation requests to your validation URL:

```json
{
    "TransactionType": "Pay Bill",
    "TransID": "QK123456789",
    "TransTime": "20240115123456",
    "TransAmount": "100.00",
    "BusinessShortCode": "174379",
    "BillRefNumber": "INV001",
    "InvoiceNumber": "",
    "OrgAccountBalance": "1000.00",
    "ThirdPartyTransID": "",
    "MSISDN": "254700000000",
    "FirstName": "John",
    "MiddleName": "",
    "LastName": "Doe"
}
```

### Handling Callbacks in Laravel

```php
// In your routes/web.php or routes/api.php

// Confirmation callback
Route::post('/mpesa/c2b/confirmation', function (Request $request) {
    $data = $request->all();
    
    // Extract payment details
    $transactionId = $data['TransID'];
    $amount = $data['TransAmount'];
    $phoneNumber = $data['MSISDN'];
    $billRefNumber = $data['BillRefNumber'];
    $businessShortCode = $data['BusinessShortCode'];
    $transactionTime = $data['TransTime'];
    
    // Process the payment confirmation
    // Update your database, send confirmation email, etc.
    
    Log::info('C2B Payment Confirmation', $data);
    
    return response()->json(['status' => 'success']);
});

// Validation callback (optional)
Route::post('/mpesa/c2b/validation', function (Request $request) {
    $data = $request->all();
    
    // Validate the payment request
    // You can implement custom validation logic here
    
    Log::info('C2B Payment Validation', $data);
    
    return response()->json(['status' => 'success']);
});
```

## Real-World Usage

### Customer Payment Flow

1. **Customer Initiates Payment**: Customer sends money to your paybill/till number
2. **M-Pesa Validates**: M-Pesa validates the payment (optional)
3. **Payment Confirmed**: M-Pesa sends confirmation to your callback URL
4. **Business Processes**: Your system processes the payment

### Example: E-commerce Integration

```php
// In your order processing controller
public function processOrder(Request $request)
{
    // Create order in your database
    $order = Order::create([
        'amount' => $request->amount,
        'customer_phone' => $request->phone,
        'reference' => 'ORDER_' . time(),
        'status' => 'pending'
    ]);
    
    // Instruct customer to pay via M-Pesa
    return response()->json([
        'message' => 'Please pay KES ' . $order->amount . ' to paybill ' . config('mpesa.lipaNaMpesaShortcode'),
        'reference' => $order->reference,
        'order_id' => $order->id
    ]);
}

// In your callback handler
public function handleC2BPayment(Request $request)
{
    $data = $request->all();
    
    // Find order by bill reference
    $order = Order::where('reference', $data['BillRefNumber'])->first();
    
    if ($order && $data['TransAmount'] == $order->amount) {
        $order->update([
            'status' => 'paid',
            'transaction_id' => $data['TransID'],
            'paid_at' => now()
        ]);
        
        // Send confirmation email, update inventory, etc.
        event(new OrderPaid($order));
    }
    
    return response()->json(['status' => 'success']);
}
```

## Security Best Practices

1. **Validate Callbacks**: Always validate callback data before processing payments.

2. **Check Amounts**: Verify that the received amount matches the expected amount.

3. **Use HTTPS**: Always use HTTPS for callback URLs in production.

4. **Implement Idempotency**: Handle duplicate callbacks gracefully.

5. **Log Transactions**: Maintain logs of all payment confirmations.

6. **Validate Bill References**: Ensure bill reference numbers are unique and valid.

## Troubleshooting

### Common Issues

1. **URLs Not Registered**: Ensure you call `c2bRegisterURLs()` before expecting payments.

2. **Invalid Shortcode**: Check that your shortcode is correctly configured.

3. **Callback URL Not Reachable**: Ensure your callback URLs are publicly accessible.

4. **Duplicate Registrations**: URLs can only be registered once per shortcode.

5. **Invalid Phone Numbers**: Ensure phone numbers are in format `254XXXXXXXXX`.

### Debugging

```php
// Check if URLs are registered
$response = $mpesa->c2bRegisterURLs();
dd($response); // Check the response

// Validate configuration
dd([
    'shortcode' => config('mpesa.lipaNaMpesaShortcode'),
    'confirmation_url' => config('mpesa.confirmationURL'),
    'validation_url' => config('mpesa.validationURL'),
]);

// Test payment simulation
$response = $mpesa->c2bPayment('CustomerPayBillOnline', 100, '254708374149', 'TEST001');
dd($response);
```

## Package Features

### What the Package Handles Automatically

✅ **URL Registration**: Automatically registers confirmation and validation URLs  
✅ **Request Building**: Constructs complete request payloads  
✅ **Authentication**: Handles all authentication automatically  
✅ **Response Parsing**: Returns clean response data  
✅ **Error Handling**: Manages common error scenarios  
✅ **Configuration Management**: Reads settings from Laravel config  

### What You Don't Need to Worry About

❌ Manual URL registration  
❌ Request payload construction  
❌ Authentication headers  
❌ Complex response parsing  
❌ Callback URL management  

## Related Documentation

- [Authorization](../security/authorization.md)
- [Getting Started](../introduction/getting-started.md)
- [Configuration](../introduction/configuration.md)
- [M-Pesa Express](./mpesa-express.md)
- [B2C Payments](./b2c.md)
- [Transaction Status](../experience/transaction-status.md)
