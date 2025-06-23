# M-Pesa Reversals API

## Overview

The M-Pesa Reversals API allows businesses to reverse transactions that have been processed through the M-Pesa system. This is useful for correcting errors, handling customer complaints, or managing failed transactions that were actually successful.

## Key Features

- **Transaction Reversal**: Reverse completed M-Pesa transactions
- **Automatic Processing**: M-Pesa handles the reversal process automatically
- **Callback Notifications**: Receive real-time updates on reversal status
- **Security**: Encrypted security credentials for secure operations
- **Queue Management**: Handle timeouts and processing delays gracefully

## Configuration

### Environment Setup

```php
// config/mpesa.php
return [
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'), // sandbox or production
    'baseUrl' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
    'initiatorUsername' => env('MPESA_INITIATOR_USERNAME', ''),
    'initiatorPassword' => env('MPESA_INITIATOR_PASSWORD', ''),
    'resultURL' => env('MPESA_RESULT_URL', 'https://your-domain.com/mpesa/reversal/result'),
    'queueTimeOutURL' => env('MPESA_QUEUE_TIMEOUT_URL', 'https://your-domain.com/mpesa/reversal/timeout'),
];
```

### Required Environment Variables

```env
MPESA_ENVIRONMENT=sandbox
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_INITIATOR_USERNAME=your_initiator_username
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_RESULT_URL=https://your-domain.com/mpesa/reversal/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/reversal/timeout
```

## Usage

### Basic Reversal Request

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

$response = $mpesa->reversals(
    transactionId: 'QK123456789',
    amount: 100,
    receiverParty: '254700000000',
    receiverIdentifierType: '1', // 1=MSISDN, 2=Till Number, 4=Shortcode
    remarks: 'Customer requested reversal',
    occasion: 'Customer complaint'
);
```

### Advanced Usage with Error Handling

```php
use Itsmurumba\Mpesa\Mpesa;
use Exception;

try {
    $mpesa = new Mpesa();
    
    $response = $mpesa->reversals(
        transactionId: 'QK123456789',
        amount: 100,
        receiverParty: '254700000000',
        receiverIdentifierType: '1',
        remarks: 'Incorrect amount charged',
        occasion: 'System error correction'
    );
    
    // Handle successful reversal request
    if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
        $conversationId = $response['ConversationID'];
        $originatorConversationId = $response['OriginatorConversationID'];
        
        // Store reversal request details
        ReversalRequest::create([
            'transaction_id' => 'QK123456789',
            'amount' => 100,
            'conversation_id' => $conversationId,
            'originator_conversation_id' => $originatorConversationId,
            'status' => 'pending'
        ]);
    }
    
} catch (Exception $e) {
    // Handle errors
    Log::error('M-Pesa reversal failed: ' . $e->getMessage());
}
```

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `transactionId` | string | Yes | The original transaction ID to reverse |
| `amount` | integer | Yes | The amount to reverse (must match original transaction) |
| `receiverParty` | string | Yes | The party receiving the reversal (phone number, till, or shortcode) |
| `receiverIdentifierType` | string | Yes | Type of receiver: 1=MSISDN, 2=Till Number, 4=Shortcode |
| `remarks` | string | Yes | Reason for the reversal |
| `occasion` | string | No | Additional context for the reversal |

## Response Format

### Success Response

```json
{
    "ConversationID": "AG_20231201_0000abcd1234",
    "OriginatorConversationID": "12345678-1234-1234-1234-123456789012",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
}
```

### Error Response

```json
{
    "ConversationID": "AG_20231201_0000abcd1234",
    "OriginatorConversationID": "12345678-1234-1234-1234-123456789012",
    "ResponseCode": "1",
    "ResponseDescription": "The initiator information is invalid."
}
```

## Callback Handling

### Result URL Callback

```php
// routes/web.php
Route::post('/mpesa/reversal/result', function (Request $request) {
    $data = $request->all();
    
    // Verify the callback
    if (isset($data['ResultCode'])) {
        switch ($data['ResultCode']) {
            case '0':
                // Reversal successful
                Log::info('Reversal successful', $data);
                break;
            case '1':
                // Reversal failed
                Log::error('Reversal failed', $data);
                break;
            default:
                // Other status codes
                Log::warning('Reversal status unknown', $data);
        }
    }
    
    return response()->json(['status' => 'received']);
});
```

### Queue Timeout URL Callback

```php
Route::post('/mpesa/reversal/timeout', function (Request $request) {
    $data = $request->all();
    
    // Handle timeout - check transaction status manually
    Log::warning('Reversal request timed out', $data);
    
    // You may want to check the transaction status manually
    // or implement retry logic
    
    return response()->json(['status' => 'timeout_handled']);
});
```

## Real-World Usage Examples

### E-commerce Refund System

```php
class RefundService
{
    public function processRefund(Order $order, $reason)
    {
        $mpesa = new Mpesa();
        
        try {
            $response = $mpesa->reversals(
                transactionId: $order->mpesa_transaction_id,
                amount: $order->amount,
                receiverParty: $order->customer_phone,
                receiverIdentifierType: '1',
                remarks: "Refund for order #{$order->id}: {$reason}",
                occasion: 'Customer refund'
            );
            
            if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
                $order->update([
                    'refund_status' => 'processing',
                    'refund_conversation_id' => $response['ConversationID']
                ]);
                
                // Notify customer
                $this->notifyCustomer($order, 'refund_initiated');
            }
            
        } catch (Exception $e) {
            Log::error("Refund failed for order {$order->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
```

### Error Correction System

```php
class TransactionCorrectionService
{
    public function correctTransaction($originalTransactionId, $correctedAmount, $reason)
    {
        $mpesa = new Mpesa();
        
        // First, reverse the incorrect transaction
        $reversalResponse = $mpesa->reversals(
            transactionId: $originalTransactionId,
            amount: $correctedAmount,
            receiverParty: '254700000000', // Customer phone
            receiverIdentifierType: '1',
            remarks: "Correction: {$reason}",
            occasion: 'System correction'
        );
        
        // Then process the correct transaction
        if (isset($reversalResponse['ResponseCode']) && $reversalResponse['ResponseCode'] === '0') {
            // Process correct transaction
            $correctResponse = $mpesa->expressPayment(
                amount: $correctedAmount,
                phoneNumber: '254700000000',
                accountReference: 'CORRECTION',
                transactionDescription: "Corrected payment: {$reason}"
            );
            
            return [
                'reversal' => $reversalResponse,
                'correction' => $correctResponse
            ];
        }
        
        throw new Exception('Failed to initiate reversal');
    }
}
```

## Security Best Practices

### 1. Validate Input Data

```php
public function validateReversalRequest($transactionId, $amount, $receiverParty)
{
    // Validate transaction ID format
    if (!preg_match('/^[A-Z0-9]{10,}$/', $transactionId)) {
        throw new InvalidArgumentException('Invalid transaction ID format');
    }
    
    // Validate amount
    if ($amount <= 0 || $amount > 70000) {
        throw new InvalidArgumentException('Invalid amount');
    }
    
    // Validate phone number
    if (!preg_match('/^254[0-9]{9}$/', $receiverParty)) {
        throw new InvalidArgumentException('Invalid phone number format');
    }
    
    return true;
}
```

### 2. Implement Rate Limiting

```php
use Illuminate\Cache\RateLimiter;

public function processReversalWithRateLimit($transactionId, $amount, $receiverParty)
{
    $rateLimiter = app(RateLimiter::class);
    $key = "reversals:{$receiverParty}";
    
    if ($rateLimiter->tooManyAttempts($key, 5)) { // 5 attempts per hour
        throw new Exception('Too many reversal attempts');
    }
    
    $rateLimiter->hit($key, 3600); // 1 hour window
    
    return $this->mpesa->reversals($transactionId, $amount, $receiverParty, '1', 'Rate limited reversal');
}
```

### 3. Audit Trail

```php
class ReversalAuditService
{
    public function logReversalRequest($data, $response)
    {
        ReversalAudit::create([
            'transaction_id' => $data['transactionId'],
            'amount' => $data['amount'],
            'receiver_party' => $data['receiverParty'],
            'remarks' => $data['remarks'],
            'request_data' => json_encode($data),
            'response_data' => json_encode($response),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now()
        ]);
    }
}
```

## Troubleshooting

### Common Issues

1. **Invalid Transaction ID**
   - Ensure the transaction ID exists and is in the correct format
   - Check that the transaction hasn't already been reversed

2. **Amount Mismatch**
   - The reversal amount must exactly match the original transaction amount
   - Verify the original transaction details before initiating reversal

3. **Receiver Party Issues**
   - Ensure the receiver party matches the original transaction
   - Verify the identifier type is correct (1=MSISDN, 2=Till, 4=Shortcode)

4. **Authentication Errors**
   - Check initiator username and password
   - Verify security credentials are properly encrypted

### Debug Mode

```php
// Enable debug logging
Log::channel('mpesa')->info('Reversal request', [
    'transaction_id' => $transactionId,
    'amount' => $amount,
    'receiver_party' => $receiverParty,
    'timestamp' => now()
]);
```

## Package Features

### Automatic Configuration

The package automatically handles:
- Environment-specific base URLs
- Security credential encryption
- Access token management
- Request/response formatting

### Error Handling

```php
try {
    $response = $mpesa->reversals($transactionId, $amount, $receiverParty, $receiverIdentifierType, $remarks);
} catch (IsNullException $e) {
    // Handle missing configuration
    Log::error('M-Pesa configuration missing: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other errors
    Log::error('M-Pesa reversal error: ' . $e->getMessage());
}
```

### Response Validation

```php
public function validateReversalResponse($response)
{
    if (!isset($response['ResponseCode'])) {
        throw new Exception('Invalid response format');
    }
    
    if ($response['ResponseCode'] !== '0') {
        throw new Exception('Reversal failed: ' . ($response['ResponseDescription'] ?? 'Unknown error'));
    }
    
    return $response;
}
```

## Testing

### Unit Tests

```php
public function test_reversal_request()
{
    $mpesa = new Mpesa();
    
    $response = $mpesa->reversals(
        'QK123456789',
        100,
        '254700000000',
        '1',
        'Test reversal',
        'Testing'
    );
    
    $this->assertArrayHasKey('ResponseCode', $response);
    $this->assertArrayHasKey('ConversationID', $response);
}
```

### Integration Tests

```php
public function test_reversal_with_real_transaction()
{
    // First create a test transaction
    $mpesa = new Mpesa();
    
    $paymentResponse = $mpesa->expressPayment(100, '254700000000');
    
    // Then reverse it
    $reversalResponse = $mpesa->reversals(
        $paymentResponse['TransactionID'],
        100,
        '254700000000',
        '1',
        'Integration test reversal'
    );
    
    $this->assertEquals('0', $reversalResponse['ResponseCode']);
}
```

## Best Practices Summary

1. **Always validate input data** before making reversal requests
2. **Implement proper error handling** and logging
3. **Use callbacks** to handle asynchronous responses
4. **Maintain audit trails** for all reversal operations
5. **Test thoroughly** in sandbox environment before production
6. **Monitor reversal success rates** and investigate failures
7. **Implement rate limiting** to prevent abuse
8. **Keep security credentials secure** and rotate regularly
