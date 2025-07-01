# M-Pesa Express (STK Push)

M-Pesa Express (also known as STK Push) is a service that allows businesses to initiate online payments on behalf of customers. It triggers an M-Pesa payment prompt on the customer's phone, enabling seamless mobile money transactions.

## Overview

M-Pesa Express enables businesses to:
- Initiate payment requests directly to customers' phones
- Receive real-time payment confirmations
- Query payment status for pending transactions
- Provide a seamless checkout experience

**Good News**: The Laravel M-Pesa package makes M-Pesa Express implementation simple and straightforward!

## How M-Pesa Express Works in This Package

### Automatic Configuration

The package automatically handles all the complex configuration:

1. **Business Shortcode**: Uses your configured Lipa na M-Pesa shortcode
2. **Password Generation**: Automatically generates the required password using your passkey
3. **Timestamp**: Generates the required timestamp in the correct format
4. **Callback URLs**: Uses your configured callback URLs for payment notifications
5. **Authorization**: Automatically handles authentication tokens

### Implementation Details

```php
// Simple usage - the package handles all the complexity
$mpesa = new Mpesa();

// Initiate payment
$response = $mpesa->expressPayment(100, '254700000000');

// Query payment status
$status = $mpesa->expressPaymentQuery('ws_CO_123456789');
```

## Configuration

### Required Configuration

Make sure your `config/mpesa.php` file contains the necessary M-Pesa Express settings:

```php
return [
    'lipaNaMpesaShortcode' => env('MPESA_LIPA_NA_MPESA_SHORTCODE'),
    'lipaNaMpesaPasskey' => env('MPESA_LIPA_NA_MPESA_PASSKEY'),
    'lipaNaMpesaCallbackURL' => env('MPESA_LIPA_NA_MPESA_CALLBACK_URL'),
    // ... other configuration
];
```

### Environment Variables

Add these to your `.env` file:

```env
MPESA_LIPA_NA_MPESA_SHORTCODE=174379
MPESA_LIPA_NA_MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
MPESA_LIPA_NA_MPESA_CALLBACK_URL=https://your-domain.com/mpesa/callback
```

## API Endpoints

### 1. Initiate Payment

**Endpoint:** `POST /mpesa/stkpush/v1/processrequest`

### 2. Query Payment Status

**Endpoint:** `POST /mpesa/stkpushquery/v1/query`

## Implementation

### Initiating a Payment

The package provides a simple method to initiate M-Pesa Express payments:

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

// Basic payment
$response = $mpesa->expressPayment(
    amount: 100,                    // Amount in KES
    phoneNumber: '254700000000'     // Customer's phone number
);

// Payment with custom details
$response = $mpesa->expressPayment(
    amount: 1000,
    phoneNumber: '254700000000',
    accountReference: 'ORDER123',           // Custom reference
    transactionDescription: 'Payment for Order #123'  // Custom description
);
```

### Behind the Scenes

When you call `expressPayment()`, the package automatically:

1. **Generates Timestamp**: Creates timestamp in `YmdHis` format
2. **Creates Password**: Generates password using `base64_encode(shortcode + passkey + timestamp)`
3. **Builds Request**: Constructs the complete request payload
4. **Sends Request**: Makes the API call with proper authentication
5. **Returns Response**: Returns the M-Pesa response

### Request Payload (Automatic)

```php
// This is handled automatically by the package
$arrayData = [
    'BusinessShortCode' => $this->lipaNaMpesaShortcode,
    'Password' => base64_encode($this->lipaNaMpesaShortcode . $this->lipaNaMpesaPasskey . $timestamp),
    'Timestamp' => $timestamp,
    'TransactionType' => 'CustomerPayBillOnline',
    'Amount' => $amount,
    'PartyA' => $phoneNumber,
    'PartyB' => $this->lipaNaMpesaShortcode,
    'PhoneNumber' => $phoneNumber,
    'CallBackURL' => $this->lipaNaMpesaCallbackURL,
    'AccountReference' => $accountReference,
    'TransactionDesc' => $transactionDescription,
];
```

### Querying Payment Status

After initiating a payment, you can query its status:

```php
// Get the CheckoutRequestID from the initial response
$initialResponse = $mpesa->expressPayment(100, '254700000000');
$checkoutRequestId = json_decode($initialResponse)->CheckoutRequestID;

// Query the payment status
$statusResponse = $mpesa->expressPaymentQuery($checkoutRequestId);
```

### Status Query Payload (Automatic)

```php
// This is handled automatically by the package
$arrayData = [
    'BusinessShortCode' => $this->lipaNaMpesaShortcode,
    'Password' => base64_encode($this->lipaNaMpesaShortcode . $this->lipaNaMpesaPasskey . $timestamp),
    'Timestamp' => $timestamp,
    'CheckoutRequestID' => $checkoutRequestId,
];
```

## Response Format

### Initiate Payment Response

**Success Response:**

```json
{
    "MerchantRequestID": "29115-34620561-1",
    "CheckoutRequestID": "ws_CO_123456789",
    "ResponseCode": "0",
    "ResponseDescription": "Success. Request accepted for processing",
    "CustomerMessage": "Success. Request accepted for processing"
}
```

### Query Payment Response

**Success Response:**

```json
{
    "ResponseCode": "0",
    "ResponseDescription": "The service request is processed successfully.",
    "MerchantRequestID": "29115-34620561-1",
    "CheckoutRequestID": "ws_CO_123456789",
    "ResultCode": "0",
    "ResultDesc": "The service request is processed successfully."
}
```

## Response Parameters

### Initiate Payment Response

| Parameter | Description | Type | Sample Values |
|-----------|-------------|------|---------------|
| `MerchantRequestID` | Unique identifier for the merchant request | String | `29115-34620561-1` |
| `CheckoutRequestID` | Unique identifier for the checkout request | String | `ws_CO_123456789` |
| `ResponseCode` | Response code from M-Pesa | String | `0` |
| `ResponseDescription` | Description of the response | String | `Success. Request accepted for processing` |
| `CustomerMessage` | Message to display to customer | String | `Success. Request accepted for processing` |

### Query Payment Response

| Parameter | Description | Type | Sample Values |
|-----------|-------------|------|---------------|
| `ResponseCode` | Response code from M-Pesa | String | `0` |
| `ResponseDescription` | Description of the response | String | `The service request is processed successfully.` |
| `MerchantRequestID` | Original merchant request ID | String | `29115-34620561-1` |
| `CheckoutRequestID` | Original checkout request ID | String | `ws_CO_123456789` |
| `ResultCode` | Result code of the payment | String | `0` |
| `ResultDesc` | Result description of the payment | String | `The service request is processed successfully.` |

## Error Responses

### Common Error Codes

**Invalid Phone Number:**
```json
{
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid PhoneNumber"
}
```

**Invalid Amount:**
```json
{
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid Amount"
}
```

**Invalid Business Shortcode:**
```json
{
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid BusinessShortCode"
}
```

## Testing

### Sandbox Testing

For testing purposes, use the sandbox environment:

```php
// Use sandbox credentials
MPESA_LIPA_NA_MPESA_SHORTCODE=174379
MPESA_LIPA_NA_MPESA_PASSKEY=bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919
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
    // Initiate payment
    $response = $mpesa->expressPayment(
        amount: 100,
        phoneNumber: '254708374149',
        accountReference: 'TEST123',
        transactionDescription: 'Test Payment'
    );
    
    $responseData = json_decode($response, true);
    
    if ($responseData['ResponseCode'] === '0') {
        // Payment initiated successfully
        $checkoutRequestId = $responseData['CheckoutRequestID'];
        
        // Query payment status after a few seconds
        sleep(5);
        $statusResponse = $mpesa->expressPaymentQuery($checkoutRequestId);
        
        echo "Payment Status: " . $statusResponse;
    } else {
        echo "Payment initiation failed: " . $responseData['ResponseDescription'];
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

## Callback Handling

### Setting Up Callbacks

Configure your callback URL in your environment:

```env
MPESA_LIPA_NA_MPESA_CALLBACK_URL=https://your-domain.com/mpesa/callback
```

### Callback Payload

When a payment is completed, M-Pesa will send a callback to your URL:

```json
{
    "Body": {
        "stkCallback": {
            "MerchantRequestID": "29115-34620561-1",
            "CheckoutRequestID": "ws_CO_123456789",
            "ResultCode": 0,
            "ResultDesc": "The service request is processed successfully.",
            "CallbackMetadata": {
                "Item": [
                    {
                        "Name": "Amount",
                        "Value": 100.00
                    },
                    {
                        "Name": "MpesaReceiptNumber",
                        "Value": "QK123456789"
                    },
                    {
                        "Name": "TransactionDate",
                        "Value": 20240115123456
                    },
                    {
                        "Name": "PhoneNumber",
                        "Value": 254700000000
                    }
                ]
            }
        }
    }
}
```

### Handling Callbacks in Laravel

```php
// In your routes/web.php or routes/api.php
Route::post('/mpesa/callback', function (Request $request) {
    $callbackData = $request->all();
    
    // Extract payment details
    $stkCallback = $callbackData['Body']['stkCallback'];
    $resultCode = $stkCallback['ResultCode'];
    $checkoutRequestId = $stkCallback['CheckoutRequestID'];
    
    if ($resultCode === 0) {
        // Payment successful
        $metadata = $stkCallback['CallbackMetadata']['Item'];
        
        $amount = collect($metadata)->firstWhere('Name', 'Amount')['Value'];
        $receiptNumber = collect($metadata)->firstWhere('Name', 'MpesaReceiptNumber')['Value'];
        $transactionDate = collect($metadata)->firstWhere('Name', 'TransactionDate')['Value'];
        $phoneNumber = collect($metadata)->firstWhere('Name', 'PhoneNumber')['Value'];
        
        // Process successful payment
        // Update your database, send confirmation email, etc.
    } else {
        // Payment failed
        $resultDesc = $stkCallback['ResultDesc'];
        // Handle failed payment
    }
    
    return response()->json(['status' => 'success']);
});
```

## Security Best Practices

1. **Validate Input**: Always validate phone numbers and amounts before sending requests.

2. **Handle Callbacks Securely**: Implement proper validation for callback requests.

3. **Store Transaction IDs**: Keep track of CheckoutRequestID for status queries.

4. **Implement Retry Logic**: Handle network failures and retry failed requests.

5. **Log Transactions**: Maintain logs of all payment attempts and responses.

6. **Use HTTPS**: Always use HTTPS for callback URLs in production.

## Troubleshooting

### Common Issues

1. **Invalid Phone Number Format**: Ensure phone numbers are in format `254XXXXXXXXX`

2. **Invalid Amount**: Amount must be a positive number

3. **Callback URL Not Reachable**: Ensure your callback URL is publicly accessible

4. **Authentication Errors**: Check your credentials and environment settings

5. **Network Timeouts**: Implement proper timeout handling

### Debugging

```php
// Enable debugging to see request/response details
$response = $mpesa->expressPayment(100, '254700000000');
dd($response); // Check the raw response

// Validate configuration
dd([
    'shortcode' => config('mpesa.lipaNaMpesaShortcode'),
    'passkey' => config('mpesa.lipaNaMpesaPasskey'),
    'callback_url' => config('mpesa.lipaNaMpesaCallbackURL'),
]);
```

## Package Features

### What the Package Handles Automatically

✅ **Password Generation**: Automatically generates the required password  
✅ **Timestamp Creation**: Creates timestamps in the correct format  
✅ **Request Building**: Constructs complete request payloads  
✅ **Authentication**: Handles all authentication automatically  
✅ **Response Parsing**: Returns clean response data  
✅ **Error Handling**: Manages common error scenarios  

### What You Don't Need to Worry About

❌ Manual password generation  
❌ Timestamp formatting  
❌ Request payload construction  
❌ Authentication headers  
❌ Complex response parsing  

## Related Documentation

- [Authorization](../security/authorization.md)
- [Getting Started](../introduction/getting-started.md)
- [Configuration](../introduction/configuration.md)
- [C2B Payments](./c2b.md)
- [B2C Payments](./b2c.md)
- [Transaction Status](../experience/transaction-status.md)
