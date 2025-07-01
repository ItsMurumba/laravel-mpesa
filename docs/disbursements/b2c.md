# B2C (Business to Customer) Payments

B2C (Business to Customer) payments allow businesses to send money directly to customers' M-Pesa accounts. This service is commonly used for salary payments, refunds, cashbacks, and other disbursements from businesses to individuals.

## Overview

B2C payments enable businesses to:
- Send money directly to customers' M-Pesa accounts
- Process salary payments and payroll
- Issue refunds and cashbacks
- Make bulk disbursements to multiple recipients
- Receive real-time payment confirmations

**Good News**: The Laravel M-Pesa package makes B2C implementation simple and straightforward!

## How B2C Works in This Package

### Automatic Configuration

The package automatically handles all the complex configuration:

1. **Initiator Credentials**: Uses your configured initiator username and password
2. **Security Credentials**: Automatically encrypts your initiator password using the appropriate certificate
3. **Business Shortcode**: Uses your configured Lipa na M-Pesa shortcode
4. **Callback URLs**: Uses your configured result and timeout URLs
5. **Authorization**: Automatically handles authentication tokens

### Implementation Details

```php
// Simple usage - the package handles all the complexity
$mpesa = new Mpesa();

// Send money to customer
$response = $mpesa->b2cPayment('SalaryPayment', 1000, '254700000000', 'Salary payment', 'Salary');
```

## Configuration

### Required Configuration

Make sure your `config/mpesa.php` file contains the necessary B2C settings:

```php
return [
    'initiatorUsername' => env('MPESA_INITIATOR_USERNAME'),
    'initiatorPassword' => env('MPESA_INITIATOR_PASSWORD'),
    'lipaNaMpesaShortcode' => env('MPESA_LIPA_NA_MPESA_SHORTCODE'),
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),
    'resultURL' => env('MPESA_RESULT_URL'),
    'queueTimeOutURL' => env('MPESA_QUEUE_TIMEOUT_URL'),
    // ... other configuration
];
```

### Environment Variables

Add these to your `.env` file:

```env
MPESA_INITIATOR_USERNAME=your_initiator_username
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_LIPA_NA_MPESA_SHORTCODE=174379
MPESA_ENVIRONMENT=sandbox
MPESA_RESULT_URL=https://your-domain.com/mpesa/b2c/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/b2c/timeout
```

### Security Certificates

The package automatically handles security credential encryption using the appropriate certificate:

- **Sandbox**: Uses `sandbox.cer` certificate
- **Production**: Uses `production.cer` certificate

## API Endpoint

### B2C Payment Request

**Endpoint:** `POST /mpesa/b2c/v1/paymentrequest`

## Implementation

### Making B2C Payments

The package provides a simple method to make B2C payments:

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

// Basic B2C payment
$response = $mpesa->b2cPayment(
    commandId: 'SalaryPayment',           // Command ID
    amount: 1000,                         // Amount in KES
    phoneNumber: '254700000000',          // Customer's phone number
    remarks: 'Salary payment'             // Payment remarks
);

// B2C payment with occasion
$response = $mpesa->b2cPayment(
    commandId: 'BusinessPayment',
    amount: 500,
    phoneNumber: '254700000000',
    remarks: 'Bonus payment',
    occassion: 'Year-end bonus'
);
```

### Behind the Scenes

When you call `b2cPayment()`, the package automatically:

1. **Encrypts Credentials**: Uses `setSecurityCredentials()` to encrypt your initiator password
2. **Builds Request**: Constructs the complete request payload with all required fields
3. **Sends Request**: Makes the API call with proper authentication
4. **Returns Response**: Returns the M-Pesa response

### Request Payload (Automatic)

```php
// This is handled automatically by the package
$arrayData = [
    'InitiatorName' => $this->initiatorUsername,
    'SecurityCredential' => $this->setSecurityCredentials(),
    'CommandID' => $commandId,
    'Amount' => $amount,
    'PartyA' => $this->lipaNaMpesaShortcode,
    'PartyB' => $phoneNumber,
    'Remarks' => $remarks,
    'QueueTimeOutURL' => $this->queueTimeOutURL,
    'ResultURL' => $this->resultURL,
    'Occassion' => $occassion,
];
```

### Security Credential Encryption

The package automatically handles the complex security credential encryption:

```php
// This happens automatically in the package
private function setSecurityCredentials()
{
    if ($this->environment == 'production') {
        $publicKey = File::get(__DIR__.'/certificates/production.cer');
    } else {
        $publicKey = File::get(__DIR__.'/certificates/sandbox.cer');
    }

    openssl_public_encrypt($this->initiatorPassword, $encryptedData, $publicKey, OPENSSL_PKCS1_PADDING);

    $securityCredential = base64_encode($encryptedData);

    return $securityCredential;
}
```

## Available Command IDs

| Command ID | Description | Use Case |
|------------|-------------|----------|
| `SalaryPayment` | Salary payment | Payroll and salary disbursements |
| `BusinessPayment` | Business payment | General business payments |
| `PromotionPayment` | Promotion payment | Promotional payments and cashbacks |
| `AccountBalance` | Account balance query | Check account balance |

## Response Format

### Success Response

**Status Code:** `200 OK`

```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
}
```

### Response Parameters

| Parameter | Description | Type | Sample Values |
|-----------|-------------|------|---------------|
| `ConversationID` | Unique conversation identifier | String | `AG_20240115_123456789` |
| `OriginatorConversationID` | Original conversation ID | String | `123456789` |
| `ResponseCode` | Response code from M-Pesa | String | `0` |
| `ResponseDescription` | Description of the response | String | `Accept the service request successfully.` |

## Error Responses

### Common Error Codes

**Invalid Phone Number:**
```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid PartyB"
}
```

**Invalid Amount:**
```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid Amount"
}
```

**Invalid Security Credentials:**
```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid SecurityCredential"
}
```

## Testing

### Sandbox Testing

For testing purposes, use the sandbox environment:

```php
// Use sandbox credentials
MPESA_ENVIRONMENT=sandbox
MPESA_INITIATOR_USERNAME=your_sandbox_initiator
MPESA_INITIATOR_PASSWORD=your_sandbox_password
MPESA_LIPA_NA_MPESA_SHORTCODE=174379
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
    // Make a B2C payment
    $response = $mpesa->b2cPayment(
        'SalaryPayment',
        1000,
        '254708374149',
        'Test salary payment',
        'Test payment'
    );
    
    $responseData = json_decode($response, true);
    
    if ($responseData['ResponseCode'] === '0') {
        echo "B2C payment initiated successfully\n";
        echo "Conversation ID: " . $responseData['ConversationID'] . "\n";
    } else {
        echo "B2C payment failed: " . $responseData['ResponseDescription'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Callback Handling

### Setting Up Callbacks

Configure your callback URLs in your environment:

```env
MPESA_RESULT_URL=https://your-domain.com/mpesa/b2c/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/b2c/timeout
```

### Result Callback

When a B2C payment is processed, M-Pesa sends a result to your result URL:

```json
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "OriginatorConversationID": "123456789",
        "ConversationID": "AG_20240115_123456789",
        "TransactionID": "QK123456789",
        "ResultParameters": {
            "ResultParameter": [
                {
                    "Key": "TransactionReceipt",
                    "Value": "QK123456789"
                },
                {
                    "Key": "TransactionAmount",
                    "Value": 1000
                },
                {
                    "Key": "B2CWorkingAccountAvailableFunds",
                    "Value": 50000
                },
                {
                    "Key": "B2CUtilityAccountAvailableFunds",
                    "Value": 10000
                },
                {
                    "Key": "TransactionCompletedDateTime",
                    "Value": "15.1.2024 12:34:56"
                },
                {
                    "Key": "ReceiverPartyPublicName",
                    "Value": "254700000000 - John Doe"
                },
                {
                    "Key": "B2CChargesPaidAccountAvailableFunds",
                    "Value": 0
                },
                {
                    "Key": "B2CRecipientIsRegisteredCustomer",
                    "Value": "Y"
                }
            ]
        }
    }
}
```

### Timeout Callback

If the payment request times out, M-Pesa sends a timeout notification:

```json
{
    "Result": {
        "ResultType": 0,
        "ResultCode": 0,
        "ResultDesc": "The service request is processed successfully.",
        "OriginatorConversationID": "123456789",
        "ConversationID": "AG_20240115_123456789",
        "TransactionID": "QK123456789"
    }
}
```

### Handling Callbacks in Laravel

```php
// In your routes/web.php or routes/api.php

// Result callback
Route::post('/mpesa/b2c/result', function (Request $request) {
    $data = $request->all();
    
    // Extract payment details
    $result = $data['Result'];
    $resultCode = $result['ResultCode'];
    $conversationId = $result['ConversationID'];
    $transactionId = $result['TransactionID'];
    
    if ($resultCode === 0) {
        // Payment successful
        $parameters = $result['ResultParameters']['ResultParameter'];
        
        $transactionReceipt = collect($parameters)->firstWhere('Key', 'TransactionReceipt')['Value'];
        $transactionAmount = collect($parameters)->firstWhere('Key', 'TransactionAmount')['Value'];
        $receiverParty = collect($parameters)->firstWhere('Key', 'ReceiverPartyPublicName')['Value'];
        
        // Process successful payment
        // Update your database, send confirmation email, etc.
        
        Log::info('B2C Payment Success', [
            'transaction_id' => $transactionId,
            'amount' => $transactionAmount,
            'receiver' => $receiverParty
        ]);
    } else {
        // Payment failed
        $resultDesc = $result['ResultDesc'];
        
        Log::error('B2C Payment Failed', [
            'conversation_id' => $conversationId,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc
        ]);
    }
    
    return response()->json(['status' => 'success']);
});

// Timeout callback
Route::post('/mpesa/b2c/timeout', function (Request $request) {
    $data = $request->all();
    
    // Handle timeout
    Log::warning('B2C Payment Timeout', $data);
    
    return response()->json(['status' => 'success']);
});
```

## Real-World Usage

### Payroll System Integration

```php
// In your payroll processing controller
public function processPayroll(Request $request)
{
    $employees = $request->employees;
    
    foreach ($employees as $employee) {
        try {
            $response = $this->mpesa->b2cPayment(
                'SalaryPayment',
                $employee['salary'],
                $employee['phone'],
                'Salary for ' . $employee['month'],
                'Monthly salary'
            );
            
            $responseData = json_decode($response, true);
            
            if ($responseData['ResponseCode'] === '0') {
                // Log successful payment
                PayrollLog::create([
                    'employee_id' => $employee['id'],
                    'amount' => $employee['salary'],
                    'conversation_id' => $responseData['ConversationID'],
                    'status' => 'initiated'
                ]);
            }
        } catch (Exception $e) {
            // Log failed payment
            Log::error('Payroll payment failed', [
                'employee' => $employee['id'],
                'error' => $e->getMessage()
            ]);
        }
    }
    
    return response()->json(['message' => 'Payroll processing completed']);
}

// In your callback handler
public function handleB2CResult(Request $request)
{
    $data = $request->all();
    $result = $data['Result'];
    
    if ($result['ResultCode'] === 0) {
        $parameters = $result['ResultParameters']['ResultParameter'];
        $transactionReceipt = collect($parameters)->firstWhere('Key', 'TransactionReceipt')['Value'];
        $transactionAmount = collect($parameters)->firstWhere('Key', 'TransactionAmount')['Value'];
        
        // Update payroll log
        $payrollLog = PayrollLog::where('conversation_id', $result['ConversationID'])->first();
        
        if ($payrollLog) {
            $payrollLog->update([
                'transaction_id' => $transactionReceipt,
                'status' => 'completed',
                'completed_at' => now()
            ]);
            
            // Send confirmation to employee
            event(new SalaryPaid($payrollLog));
        }
    }
    
    return response()->json(['status' => 'success']);
}
```

## Security Best Practices

1. **Secure Credentials**: Keep your initiator username and password secure.

2. **Certificate Management**: Ensure you have the correct certificates for your environment.

3. **Validate Callbacks**: Always validate callback data before processing payments.

4. **Use HTTPS**: Always use HTTPS for callback URLs in production.

5. **Implement Idempotency**: Handle duplicate callbacks gracefully.

6. **Log Transactions**: Maintain logs of all payment attempts and responses.

7. **Monitor Balances**: Regularly check your account balances before making payments.

## Troubleshooting

### Common Issues

1. **Invalid Security Credentials**: Check your initiator username and password.

2. **Certificate Issues**: Ensure you have the correct certificate for your environment.

3. **Insufficient Funds**: Check your account balance before making payments.

4. **Invalid Phone Numbers**: Ensure phone numbers are in format `254XXXXXXXXX`.

5. **Callback URL Not Reachable**: Ensure your callback URLs are publicly accessible.

### Debugging

```php
// Check configuration
dd([
    'initiator_username' => config('mpesa.initiatorUsername'),
    'environment' => config('mpesa.environment'),
    'shortcode' => config('mpesa.lipaNaMpesaShortcode'),
    'result_url' => config('mpesa.resultURL'),
]);

// Test B2C payment
$response = $mpesa->b2cPayment('SalaryPayment', 100, '254708374149', 'Test payment');
dd($response);

// Check if certificates exist
$sandboxCert = file_exists(__DIR__ . '/certificates/sandbox.cer');
$productionCert = file_exists(__DIR__ . '/certificates/production.cer');
dd(['sandbox' => $sandboxCert, 'production' => $productionCert]);
```

## Package Features

### What the Package Handles Automatically

✅ **Security Credential Encryption**: Automatically encrypts initiator password  
✅ **Certificate Management**: Uses correct certificate for environment  
✅ **Request Building**: Constructs complete request payloads  
✅ **Authentication**: Handles all authentication automatically  
✅ **Response Parsing**: Returns clean response data  
✅ **Error Handling**: Manages common error scenarios  
✅ **Configuration Management**: Reads settings from Laravel config  

### What You Don't Need to Worry About

❌ Manual credential encryption  
❌ Certificate file management  
❌ Request payload construction  
❌ Authentication headers  
❌ Complex response parsing  
❌ Security credential generation  

## Related Documentation

- [Authorization](../security/authorization.md)
- [Getting Started](../introduction/getting-started.md)
- [Configuration](../introduction/configuration.md)
- [M-Pesa Express](../payments/mpesa-express.md)
- [C2B Payments](../payments/c2b.md)
- [Transaction Status](../experience/transaction-status.md)
