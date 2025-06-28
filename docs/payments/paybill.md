# M-Pesa Business Pay Bill (B2B) API

## Overview

The M-Pesa Business Pay Bill (B2B) API enables businesses to pay bills directly from their business account to a paybill number or paybill store. This API allows you to pay on behalf of a consumer/requester, moving money from your MMF/Working account to the recipient's utility account.

## Key Features

- **Direct Business Payments**: Pay bills directly from business accounts
- **Consumer Proxy Payments**: Pay on behalf of consumers/requesters
- **Real-time Processing**: Instant payment processing and confirmation
- **Callback Notifications**: Receive real-time updates on payment status
- **Account References**: Support for customer account identification
- **Secure Transactions**: Encrypted security credentials for secure operations
- **Queue Management**: Handle timeouts and processing delays gracefully
- **Multiple Use Cases**: Utility bills, rent, services, and more

## Prerequisites

### Business Requirements

Before using this API, your organization must:

1. **M-Pesa Business Account**: Have an active M-Pesa business account
2. **API Access**: Obtain API credentials from Safaricom
3. **Initiator Role**: Ensure your API user has "Org Business Pay Bill API initiator" role
4. **Sufficient Balance**: Maintain adequate funds in your business account
5. **Valid Shortcode**: Have a registered business shortcode

### Required Permissions

- **Initiator Username**: M-Pesa API operator username with proper permissions
- **Security Credentials**: Encrypted password for secure authentication
- **Business Shortcode**: Valid 5-6 digit business shortcode

## Configuration

### Environment Setup

```php
// config/mpesa.php
return [
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'), // sandbox or production
    'baseUrl' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
    'initiatorUsername' => env('MPESA_INITIATOR_USERNAME', ''),
    'initiatorPassword' => env('MPESA_INITIATOR_PASSWORD', ''),
    'lipaNaMpesaShortcode' => env('MPESA_LIPA_NA_MPESA_SHORTCODE', ''),
    'resultURL' => env('MPESA_RESULT_URL', 'https://your-domain.com/mpesa/b2b/result'),
    'queueTimeOutURL' => env('MPESA_QUEUE_TIMEOUT_URL', 'https://your-domain.com/mpesa/b2b/timeout'),
];
```

### Required Environment Variables

```env
MPESA_ENVIRONMENT=sandbox
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_INITIATOR_USERNAME=your_initiator_username
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_LIPA_NA_MPESA_SHORTCODE=your_shortcode
MPESA_RESULT_URL=https://your-domain.com/mpesa/b2b/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/b2b/timeout
```

## Usage

### Basic B2B Payment

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

$response = $mpesa->b2bPayment(
    commandId: 'BusinessPayBill',
    amount: 50000,
    receiverShortcode: '123456',
    accountReference: 'BIZ-ACC-001',
    remarks: 'Business payment for services'
);
```

### Advanced Usage with Error Handling

```php
use Itsmurumba\Mpesa\Mpesa;
use Exception;

try {
    $mpesa = new Mpesa();
    
    $response = $mpesa->b2bPayment(
        commandId: 'BusinessPayBill',
        amount: 75000,
        receiverShortcode: '123456',
        accountReference: 'INV-2024-001',
        remarks: 'Payment for utility services - January 2024'
    );
    
    // Handle successful B2B payment request
    if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
        $conversationId = $response['ConversationID'];
        $originatorConversationId = $response['OriginatorConversationID'];
        
        // Store B2B payment request details
        B2BPayment::create([
            'command_id' => 'BusinessPayBill',
            'amount' => 75000,
            'receiver_shortcode' => '123456',
            'account_reference' => 'INV-2024-001',
            'conversation_id' => $conversationId,
            'originator_conversation_id' => $originatorConversationId,
            'status' => 'pending',
            'remarks' => 'Payment for utility services - January 2024'
        ]);
        
        // Log successful request
        Log::info('B2B payment initiated successfully', [
            'amount' => 75000,
            'receiver_shortcode' => '123456',
            'conversation_id' => $conversationId
        ]);
    }
    
} catch (Exception $e) {
    // Handle errors
    Log::error('B2B payment failed: ' . $e->getMessage());
    throw $e;
}
```

### Different B2B Payment Scenarios

```php
// Utility Bill Payment
$utilityPayment = $mpesa->b2bPayment(
    'BusinessPayBill',
    45000,
    '123456', // Utility company paybill
    'UTIL-ACC-001',
    'Electricity bill payment - January 2024'
);

// Rent Payment
$rentPayment = $mpesa->b2bPayment(
    'BusinessPayBill',
    120000,
    '789012', // Landlord paybill
    'RENT-ACC-001',
    'Office rent payment - Q1 2024'
);

// Service Provider Payment
$servicePayment = $mpesa->b2bPayment(
    'BusinessPayBill',
    25000,
    '345678', // Service provider paybill
    'SVC-ACC-001',
    'IT services payment - January 2024'
);

// Tax Payment
$taxPayment = $mpesa->b2bPayment(
    'BusinessPayBill',
    150000,
    '572572', // KRA paybill
    'TAX-ACC-001',
    'Corporate tax payment - Q1 2024'
);
```

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `commandId` | string | Yes | For this API use "BusinessPayBill" only |
| `amount` | integer | Yes | The transaction amount to be paid |
| `receiverShortcode` | string | Yes | The paybill number to which money will be moved |
| `accountReference` | string | Yes | Account reference for the payment (up to 13 characters) |
| `remarks` | string | Yes | Additional information (up to 100 characters) |

### Fixed Parameters (Set by API)

| Parameter | Value | Description |
|-----------|-------|-------------|
| `InitiatorName` | From config | M-Pesa API operator username |
| `SecurityCredential` | Encrypted | Encrypted initiator password |
| `SenderIdentifierType` | '4' | Type of sender shortcode (fixed) |
| `RecieverIdentifierType` | '4' | Type of receiver shortcode (fixed) |
| `PartyA` | From config | Your business shortcode |
| `QueueTimeOutURL` | From config | URL for timeout notifications |
| `ResultURL` | From config | URL for result notifications |

## Response Format

### Success Response

```json
{
    "OriginatorConversationID": "5118-111210482-1",
    "ConversationID": "AG_20230420_2010759fd5662ef6d054",
    "ResponseCode": "0",
    "ResponseDescription": "Accept the service request successfully."
}
```

### Error Response

```json
{
    "OriginatorConversationID": "5118-111210482-1",
    "ConversationID": "AG_20230420_2010759fd5662ef6d054",
    "ResponseCode": "1",
    "ResponseDescription": "The initiator information is invalid."
}
```

## Callback Handling

### Result URL Callback

```php
// routes/web.php
Route::post('/mpesa/b2b/result', function (Request $request) {
    $data = $request->all();
    
    // Verify the callback
    if (isset($data['Result'])) {
        $result = $data['Result'];
        
        switch ($result['ResultCode']) {
            case '0':
                // B2B payment successful
                $transactionId = $result['TransactionID'];
                $amount = $this->extractAmount($result['ResultParameters']);
                $receiverPartyName = $this->extractReceiverPartyName($result['ResultParameters']);
                $billReferenceNumber = $this->extractBillReferenceNumber($result['ReferenceData']);
                
                // Update B2B payment record
                B2BPayment::where('conversation_id', $result['ConversationID'])
                    ->update([
                        'status' => 'completed',
                        'transaction_id' => $transactionId,
                        'completed_at' => now()
                    ]);
                
                // Send confirmation to recipient
                $this->sendPaymentConfirmation($receiverPartyName, $amount, $transactionId);
                
                Log::info('B2B payment completed successfully', [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'receiver_party' => $receiverPartyName
                ]);
                break;
                
            case '2001':
                // B2B payment failed
                Log::error('B2B payment failed: ' . $result['ResultDesc']);
                break;
                
            default:
                // Other status codes
                Log::warning('B2B payment status unknown', $result);
        }
    }
    
    return response()->json(['status' => 'received']);
});

private function extractAmount($resultParameters)
{
    foreach ($resultParameters['ResultParameter'] as $param) {
        if ($param['Key'] === 'Amount') {
            return $param['Value'];
        }
    }
    return null;
}

private function extractReceiverPartyName($resultParameters)
{
    foreach ($resultParameters['ResultParameter'] as $param) {
        if ($param['Key'] === 'ReceiverPartyPublicName') {
            return $param['Value'];
        }
    }
    return null;
}

private function extractBillReferenceNumber($referenceData)
{
    foreach ($referenceData['ReferenceItem'] as $item) {
        if ($item['Key'] === 'BillReferenceNumber') {
            return $item['Value'];
        }
    }
    return null;
}
```

### Queue Timeout URL Callback

```php
Route::post('/mpesa/b2b/timeout', function (Request $request) {
    $data = $request->all();
    
    // Handle timeout - check transaction status manually
    Log::warning('B2B payment request timed out', $data);
    
    // You may want to check the transaction status manually
    // or implement retry logic
    
    return response()->json(['status' => 'timeout_handled']);
});
```

## Real-World Usage Examples

### Automated Utility Bill Payment System

```php
class AutomatedUtilityPaymentService
{
    public function processMonthlyUtilityPayments()
    {
        $mpesa = new Mpesa();
        
        // Get pending utility bills
        $utilityBills = UtilityBill::where('status', 'pending')
            ->where('due_date', '<=', now())
            ->get();
        
        foreach ($utilityBills as $bill) {
            try {
                $response = $mpesa->b2bPayment(
                    commandId: 'BusinessPayBill',
                    amount: $bill->amount,
                    receiverShortcode: $bill->utility_company->paybill_number,
                    accountReference: $bill->account_number,
                    remarks: "{$bill->utility_type} payment - {$bill->billing_period}"
                );
                
                if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
                    // Update bill status
                    $bill->update([
                        'status' => 'processing',
                        'conversation_id' => $response['ConversationID'],
                        'initiated_at' => now()
                    ]);
                    
                    // Send notification
                    $this->notifyUtilityPaymentInitiated($bill);
                }
                
            } catch (Exception $e) {
                Log::error("Utility payment failed for bill {$bill->id}: " . $e->getMessage());
                
                // Mark for retry
                $bill->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }
    
    private function notifyUtilityPaymentInitiated($bill)
    {
        $message = "Utility payment of KES {$bill->amount} for {$bill->utility_type} has been initiated. Account: {$bill->account_number}";
        
        // Send email notification
        Mail::to($bill->business->email)->send(new UtilityPaymentInitiatedMail($bill));
        
        // Send SMS notification
        SMS::send($bill->business->phone, $message);
    }
}
```

### Property Management Payment System

```php
class PropertyManagementPaymentService
{
    public function processRentPayment($property, $tenant, $amount)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->b2bPayment(
            commandId: 'BusinessPayBill',
            amount: $amount,
            receiverShortcode: $property->landlord->paybill_number,
            accountReference: "RENT-{$property->id}-{$tenant->id}",
            remarks: "Rent payment for {$property->address} - {$tenant->name}"
        );
        
        if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
            // Create rent payment record
            RentPayment::create([
                'property_id' => $property->id,
                'tenant_id' => $tenant->id,
                'amount' => $amount,
                'conversation_id' => $response['ConversationID'],
                'status' => 'processing'
            ]);
            
            // Update tenant payment status
            $tenant->update(['last_payment_date' => now()]);
        }
        
        return $response;
    }
    
    public function handleRentPaymentCallback($conversationId, $resultCode, $transactionId)
    {
        $rentPayment = RentPayment::where('conversation_id', $conversationId)->first();
        
        if ($rentPayment) {
            if ($resultCode === '0') {
                $rentPayment->update([
                    'status' => 'completed',
                    'transaction_id' => $transactionId,
                    'completed_at' => now()
                ]);
                
                // Send receipt to tenant
                $this->sendRentReceipt($rentPayment);
                
                // Update property management system
                $this->updatePropertyManagementSystem($rentPayment);
            } else {
                $rentPayment->update([
                    'status' => 'failed',
                    'error_message' => 'Payment failed'
                ]);
            }
        }
    }
}
```

### Corporate Tax Payment System

```php
class CorporateTaxPaymentService
{
    public function processTaxPayment($business, $taxType, $amount, $period)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->b2bPayment(
            commandId: 'BusinessPayBill',
            amount: $amount,
            receiverShortcode: '572572', // KRA paybill
            accountReference: "TAX-{$taxType}-{$business->kra_pin}-{$period}",
            remarks: "{$taxType} payment for {$period} - {$business->name}"
        );
        
        if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
            // Create tax payment record
            TaxPayment::create([
                'business_id' => $business->id,
                'tax_type' => $taxType,
                'amount' => $amount,
                'period' => $period,
                'conversation_id' => $response['ConversationID'],
                'status' => 'processing'
            ]);
            
            // Update business tax status
            $business->tax_payments()->create([
                'tax_type' => $taxType,
                'amount' => $amount,
                'period' => $period,
                'status' => 'processing'
            ]);
        }
        
        return $response;
    }
}
```

## Security Best Practices

### 1. Validate Input Data

```php
public function validateB2BPaymentRequest($commandId, $amount, $receiverShortcode, $accountReference, $remarks)
{
    // Validate command ID
    if ($commandId !== 'BusinessPayBill') {
        throw new InvalidArgumentException('Invalid command ID. Use "BusinessPayBill" only.');
    }
    
    // Validate amount
    if ($amount <= 0 || $amount > 10000000) { // 10M KES limit
        throw new InvalidArgumentException('Invalid amount');
    }
    
    // Validate receiver shortcode
    if (!preg_match('/^[0-9]{5,6}$/', $receiverShortcode)) {
        throw new InvalidArgumentException('Invalid receiver shortcode format');
    }
    
    // Validate account reference
    if (strlen($accountReference) > 13) {
        throw new InvalidArgumentException('Account reference too long (max 13 characters)');
    }
    
    // Validate remarks
    if (strlen($remarks) > 100) {
        throw new InvalidArgumentException('Remarks too long (max 100 characters)');
    }
    
    return true;
}
```

### 2. Implement Rate Limiting

```php
use Illuminate\Cache\RateLimiter;

public function processB2BPaymentWithRateLimit($commandId, $amount, $receiverShortcode, $accountReference, $remarks)
{
    $rateLimiter = app(RateLimiter::class);
    $key = "b2b_payment:{$receiverShortcode}";
    
    if ($rateLimiter->tooManyAttempts($key, 20)) { // 20 payments per hour
        throw new Exception('Too many B2B payment attempts for this receiver');
    }
    
    $rateLimiter->hit($key, 3600); // 1 hour window
    
    return $this->mpesa->b2bPayment($commandId, $amount, $receiverShortcode, $accountReference, $remarks);
}
```

### 3. Payment Verification

```php
class B2BPaymentVerificationService
{
    public function verifyPayment($conversationId)
    {
        // Check payment status in database
        $payment = B2BPayment::where('conversation_id', $conversationId)->first();
        
        if (!$payment) {
            throw new Exception('Payment not found');
        }
        
        // If payment is still pending, check with M-Pesa
        if ($payment->status === 'pending') {
            $this->checkPaymentStatusWithMpesa($payment);
        }
        
        return $payment;
    }
    
    private function checkPaymentStatusWithMpesa($payment)
    {
        // Use transaction status API to check payment
        $mpesa = new Mpesa();
        
        if ($payment->transaction_id) {
            $response = $mpesa->transactionStatus(
                transactionId: $payment->transaction_id,
                identifierType: '4',
                remarks: 'B2B payment status check',
                occasion: 'Verification'
            );
            
            // Process status response
            $this->processStatusResponse($payment, $response);
        }
    }
}
```

## Troubleshooting

### Common Issues

1. **Invalid Initiator Information**
   - Check initiator username and password
   - Verify security credentials are properly encrypted
   - Ensure API user has "Org Business Pay Bill API initiator" role

2. **Insufficient Funds**
   - Ensure business account has sufficient balance
   - Check account status and limits
   - Verify shortcode is active

3. **Invalid Receiver Shortcode**
   - Ensure receiver shortcode is valid and active
   - Check shortcode format (5-6 digits)
   - Verify receiver accepts B2B payments

4. **Account Reference Issues**
   - Ensure account reference is not longer than 13 characters
   - Use alphanumeric characters only
   - Avoid special characters

5. **Callback URL Issues**
   - Ensure callback URLs are accessible
   - Check URL format and SSL certificates
   - Verify callback handling logic

### Debug Mode

```php
// Enable debug logging
Log::channel('b2b_payment')->info('B2B payment request', [
    'command_id' => $commandId,
    'amount' => $amount,
    'receiver_shortcode' => $receiverShortcode,
    'account_reference' => $accountReference,
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
    $response = $mpesa->b2bPayment($commandId, $amount, $receiverShortcode, $accountReference, $remarks);
} catch (IsNullException $e) {
    // Handle missing configuration
    Log::error('M-Pesa configuration missing: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other errors
    Log::error('B2B payment error: ' . $e->getMessage());
}
```

### Response Validation

```php
public function validateB2BResponse($response)
{
    if (!isset($response['ResponseCode'])) {
        throw new Exception('Invalid response format');
    }
    
    if ($response['ResponseCode'] !== '0') {
        throw new Exception('B2B payment failed: ' . ($response['ResponseDescription'] ?? 'Unknown error'));
    }
    
    return $response;
}
```

## Testing

### Unit Tests

```php
public function test_b2b_payment_request()
{
    $mpesa = new Mpesa();
    
    $response = $mpesa->b2bPayment(
        'BusinessPayBill',
        50000,
        '123456',
        'TEST-ACC-001',
        'Test B2B payment'
    );
    
    $this->assertArrayHasKey('ResponseCode', $response);
    $this->assertArrayHasKey('ConversationID', $response);
    $this->assertEquals('0', $response['ResponseCode']);
}
```

### Integration Tests

```php
public function test_b2b_payment_workflow()
{
    $mpesa = new Mpesa();
    
    // Test B2B payment
    $response = $mpesa->b2bPayment(
        'BusinessPayBill',
        100000,
        '123456',
        'TEST-ACC-001',
        'Integration test payment'
    );
    
    $this->assertEquals('0', $response['ResponseCode']);
    
    // Test transaction status check
    if (isset($response['ConversationID'])) {
        // Wait for callback or check status manually
        $this->assertNotEmpty($response['ConversationID']);
    }
}
```

## Best Practices Summary

1. **Always validate input data** before making B2B payment requests
2. **Use appropriate command IDs** (BusinessPayBill only)
3. **Implement proper error handling** and logging
4. **Set up callback URLs** to handle asynchronous responses
5. **Test thoroughly** in sandbox environment before production
6. **Monitor B2B payment success rates** and investigate failures
7. **Implement rate limiting** to prevent abuse
8. **Maintain audit trails** for all B2B payments
9. **Keep security credentials secure** and rotate regularly
10. **Regularly reconcile** payments with recipient records
