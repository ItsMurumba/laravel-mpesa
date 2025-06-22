# Account Balance Query

Account Balance Query allows you to check the balance on your M-Pesa BuyGoods (Till Number) or PayBill account. This service is essential for monitoring your account funds, ensuring sufficient balance for transactions, and maintaining financial oversight of your M-Pesa business account.

## Overview

Account Balance Query enables businesses to:
- Check available balance on M-Pesa accounts
- Monitor account funds before making transactions
- Implement balance-based transaction limits
- Maintain financial oversight and reconciliation
- Prevent failed transactions due to insufficient funds

**Good News**: The Laravel M-Pesa package makes account balance queries simple and straightforward!

## How Account Balance Works in This Package

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

// Check account balance
$response = $mpesa->accountBalance(4, 'Account balance query');
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
MPESA_RESULT_URL=https://your-domain.com/mpesa/account-balance/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/account-balance/timeout
```

### Security Certificates

The package automatically handles security credential encryption using the appropriate certificate:

- **Sandbox**: Uses `sandbox.cer` certificate
- **Production**: Uses `production.cer` certificate

## API Endpoint

### Account Balance Query

**Endpoint:** `POST /mpesa/accountbalance/v1/query`

## Implementation

### Querying Account Balance

The package provides a simple method to query account balance:

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

// Basic account balance query
$response = $mpesa->accountBalance(
    identifierType: 4,                    // Identifier type
    remarks: 'Account balance query'      // Query remarks
);

// Account balance query with custom remarks
$response = $mpesa->accountBalance(
    4,
    'Daily balance check for reconciliation'
);
```

### Behind the Scenes

When you call `accountBalance()`, the package automatically:

1. **Encrypts Credentials**: Uses `setSecurityCredentials()` to encrypt your initiator password
2. **Sets Command ID**: Automatically sets `CommandID` to `'AccountBalance'`
3. **Builds Request**: Constructs the complete request payload with all required fields
4. **Sends Request**: Makes the API call with proper authentication
5. **Returns Response**: Returns the M-Pesa response

### Request Payload (Automatic)

```php
// This is handled automatically by the package
$arrayData = [
    'Initiator' => $this->initiatorUsername,
    'SecurityCredential' => $this->setSecurityCredentials(),
    'CommandID' => 'AccountBalance',
    'PartyA' => $this->lipaNaMpesaShortcode,
    'IdentifierType' => $identifierType,
    'ResultURL' => $this->resultURL,
    'QueueTimeOutURL' => $this->queueTimeOutURL,
    'Remarks' => $remarks,
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
| `1` | MSISDN (Phone Number) | Query balance by phone number |
| `2` | Till Number | Query balance by till number |
| `4` | Shortcode | Query balance by business shortcode |
| `7` | Organization | Query balance by organization |

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

**Invalid Shortcode:**
```json
{
    "ConversationID": "AG_20240115_123456789",
    "OriginatorConversationID": "123456789",
    "ResponseCode": "400002",
    "ResponseDescription": "Bad Request - Invalid PartyA"
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

### Complete Testing Example

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

try {
    // Query account balance
    $response = $mpesa->accountBalance(
        4, // Shortcode identifier type
        'Test account balance query'
    );
    
    $responseData = json_decode($response, true);
    
    if ($responseData['ResponseCode'] === '0') {
        echo "Account balance query initiated successfully\n";
        echo "Conversation ID: " . $responseData['ConversationID'] . "\n";
    } else {
        echo "Account balance query failed: " . $responseData['ResponseDescription'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Callback Handling

### Setting Up Callbacks

Configure your callback URLs in your environment:

```env
MPESA_RESULT_URL=https://your-domain.com/mpesa/account-balance/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/account-balance/timeout
```

### Result Callback

When an account balance query is processed, M-Pesa sends a result to your result URL:

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
                    "Key": "AccountBalance",
                    "Value": "50000.00"
                },
                {
                    "Key": "WorkingAccountAvailableFunds",
                    "Value": "45000.00"
                },
                {
                    "Key": "UtilityAccountAvailableFunds",
                    "Value": "5000.00"
                },
                {
                    "Key": "ChargesPaidAccountAvailableFunds",
                    "Value": "0.00"
                },
                {
                    "Key": "FloatAccountAvailableFunds",
                    "Value": "0.00"
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
Route::post('/mpesa/account-balance/result', function (Request $request) {
    $data = $request->all();
    
    // Extract query details
    $result = $data['Result'];
    $resultCode = $result['ResultCode'];
    $conversationId = $result['ConversationID'];
    $transactionId = $result['TransactionID'];
    
    if ($resultCode === 0) {
        // Query successful
        $parameters = $result['ResultParameters']['ResultParameter'];
        
        $accountBalance = collect($parameters)->firstWhere('Key', 'AccountBalance')['Value'];
        $workingAccountFunds = collect($parameters)->firstWhere('Key', 'WorkingAccountAvailableFunds')['Value'];
        $utilityAccountFunds = collect($parameters)->firstWhere('Key', 'UtilityAccountAvailableFunds')['Value'];
        $chargesPaidFunds = collect($parameters)->firstWhere('Key', 'ChargesPaidAccountAvailableFunds')['Value'];
        $floatAccountFunds = collect($parameters)->firstWhere('Key', 'FloatAccountAvailableFunds')['Value'];
        
        // Process account balance
        // Update your database, send notifications, etc.
        
        Log::info('Account Balance Query Success', [
            'transaction_id' => $transactionId,
            'account_balance' => $accountBalance,
            'working_account' => $workingAccountFunds,
            'utility_account' => $utilityAccountFunds,
            'charges_paid' => $chargesPaidFunds,
            'float_account' => $floatAccountFunds
        ]);
        
        // Store balance information
        AccountBalance::create([
            'transaction_id' => $transactionId,
            'conversation_id' => $conversationId,
            'account_balance' => $accountBalance,
            'working_account_funds' => $workingAccountFunds,
            'utility_account_funds' => $utilityAccountFunds,
            'charges_paid_funds' => $chargesPaidFunds,
            'float_account_funds' => $floatAccountFunds,
            'queried_at' => now()
        ]);
    } else {
        // Query failed
        $resultDesc = $result['ResultDesc'];
        
        Log::error('Account Balance Query Failed', [
            'conversation_id' => $conversationId,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc
        ]);
    }
    
    return response()->json(['status' => 'success']);
});

// Timeout callback
Route::post('/mpesa/account-balance/timeout', function (Request $request) {
    $data = $request->all();
    
    // Handle timeout
    Log::warning('Account Balance Query Timeout', $data);
    
    return response()->json(['status' => 'success']);
});
```

## Real-World Usage

### Balance Monitoring System

```php
// In your balance monitoring controller
public function checkBalance(Request $request)
{
    try {
        $response = $this->mpesa->accountBalance(
            4, // Shortcode identifier type
            'Balance check for transaction processing'
        );
        
        $responseData = json_decode($response, true);
        
        if ($responseData['ResponseCode'] === '0') {
            // Query initiated successfully
            BalanceQuery::create([
                'conversation_id' => $responseData['ConversationID'],
                'status' => 'pending',
                'query_type' => 'transaction_processing'
            ]);
            
            return response()->json([
                'message' => 'Account balance query initiated',
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
public function handleAccountBalanceResult(Request $request)
{
    $data = $request->all();
    $result = $data['Result'];
    
    if ($result['ResultCode'] === 0) {
        $parameters = $result['ResultParameters']['ResultParameter'];
        $accountBalance = collect($parameters)->firstWhere('Key', 'AccountBalance')['Value'];
        $workingAccountFunds = collect($parameters)->firstWhere('Key', 'WorkingAccountAvailableFunds')['Value'];
        
        // Update balance query record
        $balanceQuery = BalanceQuery::where('conversation_id', $result['ConversationID'])->first();
        
        if ($balanceQuery) {
            $balanceQuery->update([
                'status' => 'completed',
                'account_balance' => $accountBalance,
                'working_account_funds' => $workingAccountFunds,
                'completed_at' => now()
            ]);
            
            // Check if balance is sufficient for transactions
            if ($workingAccountFunds < 1000) {
                // Send low balance alert
                event(new LowBalanceAlert($balanceQuery));
            }
        }
    }
    
    return response()->json(['status' => 'success']);
}
```

### Daily Balance Reconciliation

```php
// Daily balance reconciliation process
public function dailyBalanceReconciliation()
{
    try {
        // Query account balance
        $response = $this->mpesa->accountBalance(
            4,
            'Daily balance reconciliation'
        );
        
        $responseData = json_decode($response, true);
        
        if ($responseData['ResponseCode'] === '0') {
            // Log reconciliation query
            ReconciliationLog::create([
                'conversation_id' => $responseData['ConversationID'],
                'type' => 'balance_reconciliation',
                'status' => 'query_initiated',
                'date' => today()
            ]);
        }
    } catch (Exception $e) {
        Log::error('Daily balance reconciliation failed', [
            'error' => $e->getMessage()
        ]);
    }
}

// In your callback handler for reconciliation
public function handleReconciliationResult(Request $request)
{
    $data = $request->all();
    $result = $data['Result'];
    
    if ($result['ResultCode'] === 0) {
        $parameters = $result['ResultParameters']['ResultParameter'];
        $accountBalance = collect($parameters)->firstWhere('Key', 'AccountBalance')['Value'];
        $workingAccountFunds = collect($parameters)->firstWhere('Key', 'WorkingAccountAvailableFunds')['Value'];
        $utilityAccountFunds = collect($parameters)->firstWhere('Key', 'UtilityAccountAvailableFunds')['Value'];
        
        // Update reconciliation log
        $reconciliationLog = ReconciliationLog::where('conversation_id', $result['ConversationID'])->first();
        
        if ($reconciliationLog) {
            $reconciliationLog->update([
                'status' => 'completed',
                'account_balance' => $accountBalance,
                'working_account_funds' => $workingAccountFunds,
                'utility_account_funds' => $utilityAccountFunds,
                'completed_at' => now()
            ]);
            
            // Generate reconciliation report
            $this->generateReconciliationReport($reconciliationLog);
        }
    }
}

private function generateReconciliationReport($reconciliationLog)
{
    // Generate daily reconciliation report
    $report = [
        'date' => $reconciliationLog->date,
        'account_balance' => $reconciliationLog->account_balance,
        'working_account_funds' => $reconciliationLog->working_account_funds,
        'utility_account_funds' => $reconciliationLog->utility_account_funds,
        'total_transactions' => Transaction::whereDate('created_at', $reconciliationLog->date)->count(),
        'total_amount' => Transaction::whereDate('created_at', $reconciliationLog->date)->sum('amount')
    ];
    
    // Send report to stakeholders
    event(new ReconciliationReportGenerated($report));
}
```

### Transaction Pre-Check System

```php
// Before processing transactions, check balance
public function processTransaction(Request $request)
{
    // Check account balance first
    try {
        $balanceResponse = $this->mpesa->accountBalance(
            4,
            'Pre-transaction balance check'
        );
        
        $balanceData = json_decode($balanceResponse, true);
        
        if ($balanceData['ResponseCode'] === '0') {
            // Store balance check for later processing
            BalanceCheck::create([
                'conversation_id' => $balanceData['ConversationID'],
                'transaction_amount' => $request->amount,
                'status' => 'pending'
            ]);
            
            return response()->json([
                'message' => 'Balance check initiated. Transaction will be processed once balance is confirmed.',
                'conversation_id' => $balanceData['ConversationID']
            ]);
        }
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Balance check failed: ' . $e->getMessage()
        ], 500);
    }
}

// In your balance callback handler
public function handleBalanceCheckResult(Request $request)
{
    $data = $request->all();
    $result = $data['Result'];
    
    if ($result['ResultCode'] === 0) {
        $parameters = $result['ResultParameters']['ResultParameter'];
        $workingAccountFunds = collect($parameters)->firstWhere('Key', 'WorkingAccountAvailableFunds')['Value'];
        
        // Find pending balance check
        $balanceCheck = BalanceCheck::where('conversation_id', $result['ConversationID'])->first();
        
        if ($balanceCheck) {
            $balanceCheck->update([
                'status' => 'completed',
                'available_funds' => $workingAccountFunds,
                'completed_at' => now()
            ]);
            
            // Check if sufficient funds
            if ($workingAccountFunds >= $balanceCheck->transaction_amount) {
                // Process the transaction
                $this->processPendingTransaction($balanceCheck);
            } else {
                // Insufficient funds
                event(new InsufficientFundsAlert($balanceCheck));
            }
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

6. **Log Queries**: Maintain logs of all account balance queries and responses.

7. **Rate Limiting**: Implement rate limiting for balance queries.

8. **Balance Monitoring**: Set up alerts for low balance situations.

## Troubleshooting

### Common Issues

1. **Invalid Security Credentials**: Check your initiator username and password.

2. **Certificate Issues**: Ensure you have the correct certificate for your environment.

3. **Invalid Identifier Type**: Use the correct identifier type for your query.

4. **Invalid Shortcode**: Check that your shortcode is correctly configured.

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

// Test account balance query
$response = $mpesa->accountBalance(4, 'Test query');
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
✅ **Command ID Setting**: Automatically sets CommandID to 'AccountBalance'  
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
- [Transaction Status](./transaction-status.md)
- [M-Pesa Express](../payments/mpesa-express.md)
- [C2B Payments](../payments/c2b.md)
- [B2C Payments](../disbursements/b2c.md)