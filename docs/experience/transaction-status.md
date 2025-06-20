# Transaction Status Query

Transaction Status Query allows you to check the status of any M-Pesa transaction using the transaction ID. This service is essential for tracking payments, verifying successful transactions, and handling failed payments in your application.

## Overview

Transaction Status Query enables businesses to:
- Check the status of any M-Pesa transaction
- Verify successful payments
- Handle failed or pending transactions
- Track transaction history
- Implement reconciliation processes

**Good News**: The Laravel M-Pesa package makes transaction status queries simple and straightforward!

## How Transaction Status Works in This Package

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

// Check transaction status
$response = $mpesa->transactionStatus('QK123456789', 1, 'Transaction status query');
```

## Configuration

### Required Configuration

Make sure your `config/mpesa.php` file contains the necessary settings:

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
MPESA_RESULT_URL=https://your-domain.com/mpesa/transaction-status/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/transaction-status/timeout
```

### Security Certificates

The package automatically handles security credential encryption using the appropriate certificate:

- **Sandbox**: Uses `sandbox.cer` certificate
- **Production**: Uses `production.cer` certificate

## API Endpoint

### Transaction Status Query

**Endpoint:** `POST /mpesa/transactionstatus/v1/query`

## Implementation

### Querying Transaction Status

The package provides a simple method to query transaction status:

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

// Basic transaction status query
$response = $mpesa->transactionStatus(
    transactionId: 'QK123456789',         // M-Pesa transaction ID
    identifierType: 1,                    // Identifier type
    remarks: 'Transaction status query'   // Query remarks
);

// Transaction status query with occasion
$response = $mpesa->transactionStatus(
    'QK123456789',
    1,
    'Payment verification',
    'Order confirmation'
);
```

### Behind the Scenes

When you call `transactionStatus()`, the package automatically:

1. **Encrypts Credentials**: Uses `setSecurityCredentials()` to encrypt your initiator password
2. **Sets Command ID**: Automatically sets `CommandID` to `'TransactionStatusQuery'`
3. **Builds Request**: Constructs the complete request payload with all required fields
4. **Sends Request**: Makes the API call with proper authentication
5. **Returns Response**: Returns the M-Pesa response

### Request Payload (Automatic)

```php
// This is handled automatically by the package
$arrayData = [
    'Initiator' => $this->initiatorUsername,
    'SecurityCredential' => $this->setSecurityCredentials(),
    'CommandID' => 'TransactionStatusQuery',
    'TransactionID' => $transactionId,
    'PartyA' => $this->lipaNaMpesaShortcode,
    'IdentifierType' => $identifierType,
    'ResultURL' => $this->resultURL,
    'QueueTimeOutURL' => $this->queueTimeOutURL,
    'Remarks' => $remarks,
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

## Identifier Types

| Identifier Type | Description | Use Case |
|----------------|-------------|----------|
| `1` | MSISDN (Phone Number) | Query by customer phone number |
| `2` | Till Number | Query by till number |
| `4` | Shortcode | Query by business shortcode |
| `7` | Organization | Query by organization |

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

**Invalid Transaction ID:**
```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid TransactionID"
}
```

**Invalid Identifier Type:**
```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid IdentifierType"
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

### Test Transaction IDs

Use these test transaction IDs for sandbox testing:
- `QK123456789` - Valid transaction ID
- `INVALID_ID` - Invalid transaction ID for error testing

### Complete Testing Example

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

try {
    // Query transaction status
    $response = $mpesa->transactionStatus(
        'QK123456789',
        1,
        'Test transaction status query',
        'Testing'
    );
    
    $responseData = json_decode($response, true);
    
    if ($responseData['ResponseCode'] === '0') {
        echo "Transaction status query initiated successfully\n";
        echo "Conversation ID: " . $responseData['ConversationID'] . "\n";
    } else {
        echo "Transaction status query failed: " . $responseData['ResponseDescription'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Callback Handling

### Setting Up Callbacks

Configure your callback URLs in your environment:

```env
MPESA_RESULT_URL=https://your-domain.com/mpesa/transaction-status/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/transaction-status/timeout
```

### Result Callback

When a transaction status query is processed, M-Pesa sends a result to your result URL:

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
                    "Key": "ReceiptNo",
                    "Value": "QK123456789"
                },
                {
                    "Key": "TransactionStatus",
                    "Value": "Success"
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

If the query request times out, M-Pesa sends a timeout notification:

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
Route::post('/mpesa/transaction-status/result', function (Request $request) {
    $data = $request->all();
    
    // Extract query details
    $result = $data['Result'];
    $resultCode = $result['ResultCode'];
    $conversationId = $result['ConversationID'];
    $transactionId = $result['TransactionID'];
    
    if ($resultCode === 0) {
        // Query successful
        $parameters = $result['ResultParameters']['ResultParameter'];
        
        $receiptNo = collect($parameters)->firstWhere('Key', 'ReceiptNo')['Value'];
        $transactionStatus = collect($parameters)->firstWhere('Key', 'TransactionStatus')['Value'];
        $transactionAmount = collect($parameters)->firstWhere('Key', 'TransactionAmount')['Value'];
        $transactionDateTime = collect($parameters)->firstWhere('Key', 'TransactionCompletedDateTime')['Value'];
        
        // Process transaction status
        // Update your database, send notifications, etc.
        
        Log::info('Transaction Status Query Success', [
            'transaction_id' => $transactionId,
            'status' => $transactionStatus,
            'amount' => $transactionAmount,
            'datetime' => $transactionDateTime
        ]);
    } else {
        // Query failed
        $resultDesc = $result['ResultDesc'];
        
        Log::error('Transaction Status Query Failed', [
            'conversation_id' => $conversationId,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc
        ]);
    }
    
    return response()->json(['status' => 'success']);
});

// Timeout callback
Route::post('/mpesa/transaction-status/timeout', function (Request $request) {
    $data = $request->all();
    
    // Handle timeout
    Log::warning('Transaction Status Query Timeout', $data);
    
    return response()->json(['status' => 'success']);
});
```

## Real-World Usage

### Payment Verification System

```php
// In your payment verification controller
public function verifyPayment(Request $request)
{
    $transactionId = $request->transaction_id;
    
    try {
        $response = $this->mpesa->transactionStatus(
            $transactionId,
            1, // MSISDN identifier type
            'Payment verification for order',
            'Order verification'
        );
        
        $responseData = json_decode($response, true);
        
        if ($responseData['ResponseCode'] === '0') {
            // Query initiated successfully
            TransactionQuery::create([
                'transaction_id' => $transactionId,
                'conversation_id' => $responseData['ConversationID'],
                'status' => 'pending',
                'query_type' => 'payment_verification'
            ]);
            
            return response()->json([
                'message' => 'Transaction status query initiated',
                'conversation_id' => $responseData['ConversationID']
            ]);
        } else {
            return response()->json([
                'error' => 'Failed to initiate query: ' . $responseData['ResponseDescription']
            ], 400);
        }
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Query failed: ' . $e->getMessage()
        ], 500);
    }
}

// In your callback handler
public function handleTransactionStatusResult(Request $request)
{
    $data = $request->all();
    $result = $data['Result'];
    
    if ($result['ResultCode'] === 0) {
        $parameters = $result['ResultParameters']['ResultParameter'];
        $transactionStatus = collect($parameters)->firstWhere('Key', 'TransactionStatus')['Value'];
        $transactionAmount = collect($parameters)->firstWhere('Key', 'TransactionAmount')['Value'];
        $receiptNo = collect($parameters)->firstWhere('Key', 'ReceiptNo')['Value'];
        
        // Update transaction query record
        $transactionQuery = TransactionQuery::where('conversation_id', $result['ConversationID'])->first();
        
        if ($transactionQuery) {
            $transactionQuery->update([
                'status' => 'completed',
                'transaction_status' => $transactionStatus,
                'amount' => $transactionAmount,
                'receipt_no' => $receiptNo,
                'completed_at' => now()
            ]);
            
            // Handle different transaction statuses
            switch ($transactionStatus) {
                case 'Success':
                    // Payment successful - update order status
                    $this->handleSuccessfulPayment($transactionQuery);
                    break;
                case 'Failed':
                    // Payment failed - notify customer
                    $this->handleFailedPayment($transactionQuery);
                    break;
                case 'Pending':
                    // Payment pending - wait for further updates
                    $this->handlePendingPayment($transactionQuery);
                    break;
            }
        }
    }
    
    return response()->json(['status' => 'success']);
}

private function handleSuccessfulPayment($transactionQuery)
{
    // Find related order and mark as paid
    $order = Order::where('transaction_id', $transactionQuery->transaction_id)->first();
    
    if ($order) {
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_receipt' => $transactionQuery->receipt_no
        ]);
        
        // Send confirmation email
        event(new OrderPaid($order));
    }
}

private function handleFailedPayment($transactionQuery)
{
    // Find related order and mark as payment failed
    $order = Order::where('transaction_id', $transactionQuery->transaction_id)->first();
    
    if ($order) {
        $order->update(['status' => 'payment_failed']);
        
        // Send failure notification
        event(new PaymentFailed($order));
    }
}
```

### Reconciliation System

```php
// Daily reconciliation process
public function dailyReconciliation()
{
    // Get all pending transactions from today
    $pendingTransactions = Transaction::where('status', 'pending')
        ->whereDate('created_at', today())
        ->get();
    
    foreach ($pendingTransactions as $transaction) {
        try {
            // Query transaction status
            $response = $this->mpesa->transactionStatus(
                $transaction->transaction_id,
                1,
                'Daily reconciliation',
                'Reconciliation'
            );
            
            $responseData = json_decode($response, true);
            
            if ($responseData['ResponseCode'] === '0') {
                // Log reconciliation query
                ReconciliationLog::create([
                    'transaction_id' => $transaction->transaction_id,
                    'conversation_id' => $responseData['ConversationID'],
                    'status' => 'query_initiated'
                ]);
            }
        } catch (Exception $e) {
            Log::error('Reconciliation query failed', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
```

## Security Best Practices

1. **Secure Credentials**: Keep your initiator username and password secure.

2. **Certificate Management**: Ensure you have the correct certificates for your environment.

3. **Validate Callbacks**: Always validate callback data before processing results.

4. **Use HTTPS**: Always use HTTPS for callback URLs in production.

5. **Implement Idempotency**: Handle duplicate callbacks gracefully.

6. **Log Queries**: Maintain logs of all transaction status queries and responses.

7. **Rate Limiting**: Implement rate limiting for transaction status queries.

## Troubleshooting

### Common Issues

1. **Invalid Transaction ID**: Ensure the transaction ID is valid and exists.

2. **Invalid Security Credentials**: Check your initiator username and password.

3. **Certificate Issues**: Ensure you have the correct certificate for your environment.

4. **Invalid Identifier Type**: Use the correct identifier type for your query.

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

// Test transaction status query
$response = $mpesa->transactionStatus('QK123456789', 1, 'Test query');
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
✅ **Command ID Setting**: Automatically sets CommandID to 'TransactionStatusQuery'  
✅ **Request Building**: Constructs complete request payloads  
✅ **Authentication**: Handles all authentication automatically  
✅ **Response Parsing**: Returns clean response data  
✅ **Error Handling**: Manages common error scenarios  
✅ **Configuration Management**: Reads settings from Laravel config  

### What You Don't Need to Worry About

❌ Manual credential encryption  
❌ Certificate file management  
❌ Command ID specification  
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
- [B2C Payments](../disbursements/b2c.md)
