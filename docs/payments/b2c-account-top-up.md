# B2C Account Top Up

## Overview

The M-Pesa B2C Account Top Up API enables businesses to load funds to a B2C shortcode directly for disbursement. This API allows you to transfer money from your MMF/Working account to the recipient's utility account, providing a way to top up B2C accounts for bulk disbursements.

## Key Features

- **B2C Account Funding**: Load funds directly to B2C shortcodes for disbursement
- **Bulk Disbursement Support**: Enable bulk payment capabilities
- **Real-time Processing**: Instant account top-up and confirmation
- **Callback Notifications**: Receive real-time updates on top-up status
- **Account References**: Support for transaction tracking and identification
- **Secure Transactions**: Encrypted security credentials for secure operations
- **Queue Management**: Handle timeouts and processing delays gracefully
- **Multiple Use Cases**: Payroll, bulk payments, disbursements, and more

## Prerequisites

### Business Requirements

Before using this API, your organization must:

1. **M-Pesa Business Account**: Have an active M-Pesa business account
2. **API Access**: Obtain API credentials from Safaricom
3. **Initiator Role**: Ensure your API user has "Org Business Pay to Bulk API initiator" role
4. **Sufficient Balance**: Maintain adequate funds in your business account
5. **Valid Shortcode**: Have a registered business shortcode
6. **B2C Shortcode**: Have access to a B2C shortcode for disbursements

### Required Permissions

- **Initiator Username**: M-Pesa API operator username with proper permissions
- **Security Credentials**: Encrypted password for secure authentication
- **Business Shortcode**: Valid 5-6 digit business shortcode
- **B2C Shortcode Access**: Access to B2C shortcode for disbursements

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
    'resultURL' => env('MPESA_RESULT_URL', 'https://your-domain.com/mpesa/b2c-topup/result'),
    'queueTimeOutURL' => env('MPESA_QUEUE_TIMEOUT_URL', 'https://your-domain.com/mpesa/b2c-topup/timeout'),
];
```

### Required Environment Variables

```env
MPESA_ENVIRONMENT=sandbox
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_INITIATOR_USERNAME=your_initiator_username
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_LIPA_NA_MPESA_SHORTCODE=your_shortcode
MPESA_RESULT_URL=https://your-domain.com/mpesa/b2c-topup/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/b2c-topup/timeout
```

## Usage

### Basic B2C Account Top Up

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

$response = $mpesa->b2bPayment(
    commandId: 'BusinessPayToBulk',
    amount: 100000,
    receiverShortcode: '600000',
    accountReference: 'B2C-TOPUP-001',
    remarks: 'B2C account top up for disbursements'
);
```

### Advanced Usage with Error Handling

```php
use Itsmurumba\Mpesa\Mpesa;
use Exception;

try {
    $mpesa = new Mpesa();
    
    $response = $mpesa->b2bPayment(
        commandId: 'BusinessPayToBulk',
        amount: 500000,
        receiverShortcode: '600000',
        accountReference: 'B2C-TOPUP-2024-001',
        remarks: 'B2C account top up for January 2024 payroll disbursements'
    );
    
    // Handle successful B2C top-up request
    if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
        $conversationId = $response['ConversationID'];
        $originatorConversationId = $response['OriginatorConversationID'];
        
        // Store B2C top-up request details
        B2CTopUp::create([
            'command_id' => 'BusinessPayToBulk',
            'amount' => 500000,
            'receiver_shortcode' => '600000',
            'account_reference' => 'B2C-TOPUP-2024-001',
            'conversation_id' => $conversationId,
            'originator_conversation_id' => $originatorConversationId,
            'status' => 'pending',
            'remarks' => 'B2C account top up for January 2024 payroll disbursements'
        ]);
        
        // Log successful request
        Log::info('B2C account top up initiated successfully', [
            'amount' => 500000,
            'receiver_shortcode' => '600000',
            'conversation_id' => $conversationId
        ]);
    }
    
} catch (Exception $e) {
    // Handle errors
    Log::error('B2C account top up failed: ' . $e->getMessage());
    throw $e;
}
```

### Different B2C Top Up Scenarios

```php
// Payroll Top Up
$payrollTopUp = $mpesa->b2bPayment(
    'BusinessPayToBulk',
    1000000,
    '600000', // B2C shortcode
    'PAYROLL-TOPUP-001',
    'Payroll account top up for February 2024'
);

// Emergency Fund Top Up
$emergencyTopUp = $mpesa->b2bPayment(
    'BusinessPayToBulk',
    250000,
    '600000', // B2C shortcode
    'EMERGENCY-TOPUP-001',
    'Emergency fund top up for urgent disbursements'
);

// Bonus Payment Top Up
$bonusTopUp = $mpesa->b2bPayment(
    'BusinessPayToBulk',
    750000,
    '600000', // B2C shortcode
    'BONUS-TOPUP-001',
    'Bonus payment account top up for year-end bonuses'
);

// Loan Disbursement Top Up
$loanTopUp = $mpesa->b2bPayment(
    'BusinessPayToBulk',
    300000,
    '600000', // B2C shortcode
    'LOAN-TOPUP-001',
    'Loan disbursement account top up for microfinance loans'
);
```

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `commandId` | string | Yes | For this API use "BusinessPayToBulk" only |
| `amount` | integer | Yes | The transaction amount to be transferred |
| `receiverShortcode` | string | Yes | The B2C shortcode to which money will be moved |
| `accountReference` | string | Yes | Account reference for the transaction (up to 13 characters) |
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
Route::post('/mpesa/b2c-topup/result', function (Request $request) {
    $data = $request->all();
    
    // Verify the callback
    if (isset($data['Result'])) {
        $result = $data['Result'];
        
        switch ($result['ResultCode']) {
            case '0':
                // B2C top-up successful
                $transactionId = $result['TransactionID'];
                $amount = $this->extractAmount($result['ResultParameters']);
                $receiverPartyName = $this->extractReceiverPartyName($result['ResultParameters']);
                $billReferenceNumber = $this->extractBillReferenceNumber($result['ReferenceData']);
                
                // Update B2C top-up record
                B2CTopUp::where('conversation_id', $result['ConversationID'])
                    ->update([
                        'status' => 'completed',
                        'transaction_id' => $transactionId,
                        'completed_at' => now()
                    ]);
                
                // Send confirmation to administrator
                $this->sendTopUpConfirmation($receiverPartyName, $amount, $transactionId);
                
                Log::info('B2C account top up completed successfully', [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'receiver_party' => $receiverPartyName
                ]);
                break;
                
            case '2001':
                // B2C top-up failed
                Log::error('B2C account top up failed: ' . $result['ResultDesc']);
                break;
                
            default:
                // Other status codes
                Log::warning('B2C account top up status unknown', $result);
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
Route::post('/mpesa/b2c-topup/timeout', function (Request $request) {
    $data = $request->all();
    
    // Handle timeout - check transaction status manually
    Log::warning('B2C account top up request timed out', $data);
    
    // You may want to check the transaction status manually
    // or implement retry logic
    
    return response()->json(['status' => 'timeout_handled']);
});
```

## Real-World Usage Examples

### Automated Payroll Top Up System

```php
class AutomatedPayrollTopUpService
{
    public function processPayrollTopUps()
    {
        $mpesa = new Mpesa();
        
        // Get pending payroll top-ups
        $payrollTopUps = PayrollTopUp::where('status', 'pending')
            ->where('due_date', '<=', now())
            ->get();
        
        foreach ($payrollTopUps as $topUp) {
            try {
                $response = $mpesa->b2bPayment(
                    commandId: 'BusinessPayToBulk',
                    amount: $topUp->amount,
                    receiverShortcode: $topUp->b2c_shortcode,
                    accountReference: "PAYROLL-{$topUp->id}",
                    remarks: "Payroll top up for {$topUp->payroll_period}"
                );
                
                if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
                    // Update top-up status
                    $topUp->update([
                        'status' => 'processing',
                        'conversation_id' => $response['ConversationID'],
                        'initiated_at' => now()
                    ]);
                    
                    // Send notification
                    $this->notifyPayrollTopUpInitiated($topUp);
                }
                
            } catch (Exception $e) {
                Log::error("Payroll top up failed for top-up {$topUp->id}: " . $e->getMessage());
                
                // Mark for retry
                $topUp->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }
    
    private function notifyPayrollTopUpInitiated($topUp)
    {
        $message = "Payroll top up of KES {$topUp->amount} has been initiated for {$topUp->payroll_period}";
        
        // Send email notification
        Mail::to($topUp->hr_manager->email)->send(new PayrollTopUpInitiatedMail($topUp));
        
        // Send SMS notification
        SMS::send($topUp->hr_manager->phone, $message);
    }
}
```

### Emergency Fund Management System

```php
class EmergencyFundManagementService
{
    public function processEmergencyTopUp($amount, $reason)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->b2bPayment(
            commandId: 'BusinessPayToBulk',
            amount: $amount,
            receiverShortcode: $this->getB2CShortcode(),
            accountReference: "EMERGENCY-" . date('YmdHis'),
            remarks: "Emergency fund top up - {$reason}"
        );
        
        if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
            // Create emergency top-up record
            EmergencyTopUp::create([
                'amount' => $amount,
                'reason' => $reason,
                'conversation_id' => $response['ConversationID'],
                'status' => 'processing'
            ]);
            
            // Update emergency fund status
            $this->updateEmergencyFundStatus($amount);
        }
        
        return $response;
    }
    
    public function handleEmergencyTopUpCallback($conversationId, $resultCode, $transactionId)
    {
        $emergencyTopUp = EmergencyTopUp::where('conversation_id', $conversationId)->first();
        
        if ($emergencyTopUp) {
            if ($resultCode === '0') {
                $emergencyTopUp->update([
                    'status' => 'completed',
                    'transaction_id' => $transactionId,
                    'completed_at' => now()
                ]);
                
                // Send emergency notification
                $this->sendEmergencyTopUpNotification($emergencyTopUp);
                
                // Update emergency fund records
                $this->updateEmergencyFundRecords($emergencyTopUp);
            } else {
                $emergencyTopUp->update([
                    'status' => 'failed',
                    'error_message' => 'Top up failed'
                ]);
            }
        }
    }
}
```

### Loan Disbursement System

```php
class LoanDisbursementService
{
    public function processLoanDisbursementTopUp($loanAmount, $loanType)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->b2bPayment(
            commandId: 'BusinessPayToBulk',
            amount: $loanAmount,
            receiverShortcode: $this->getB2CShortcode(),
            accountReference: "LOAN-{$loanType}-" . date('Ymd'),
            remarks: "Loan disbursement top up for {$loanType} loans"
        );
        
        if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
            // Create loan disbursement record
            LoanDisbursement::create([
                'loan_amount' => $loanAmount,
                'loan_type' => $loanType,
                'conversation_id' => $response['ConversationID'],
                'status' => 'processing'
            ]);
            
            // Update loan disbursement status
            $this->updateLoanDisbursementStatus($loanType);
        }
        
        return $response;
    }
    
    public function handleLoanDisbursementCallback($conversationId, $resultCode, $transactionId)
    {
        $loanDisbursement = LoanDisbursement::where('conversation_id', $conversationId)->first();
        
        if ($loanDisbursement) {
            if ($resultCode === '0') {
                $loanDisbursement->update([
                    'status' => 'completed',
                    'transaction_id' => $transactionId,
                    'completed_at' => now()
                ]);
                
                // Process loan disbursements
                $this->processLoanDisbursements($loanDisbursement);
                
                // Send disbursement confirmation
                $this->sendLoanDisbursementConfirmation($loanDisbursement);
            } else {
                $loanDisbursement->update([
                    'status' => 'failed',
                    'error_message' => 'Disbursement failed'
                ]);
            }
        }
    }
}
```

## Security Best Practices

### 1. Validate Input Data

```php
public function validateB2CTopUpRequest($commandId, $amount, $receiverShortcode, $accountReference, $remarks)
{
    // Validate command ID
    if ($commandId !== 'BusinessPayToBulk') {
        throw new InvalidArgumentException('Invalid command ID. Use "BusinessPayToBulk" only.');
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

public function processB2CTopUpWithRateLimit($commandId, $amount, $receiverShortcode, $accountReference, $remarks)
{
    $rateLimiter = app(RateLimiter::class);
    $key = "b2c_topup:{$receiverShortcode}";
    
    if ($rateLimiter->tooManyAttempts($key, 5)) { // 5 top-ups per hour
        throw new Exception('Too many B2C top-up attempts for this shortcode');
    }
    
    $rateLimiter->hit($key, 3600); // 1 hour window
    
    return $this->mpesa->b2bPayment($commandId, $amount, $receiverShortcode, $accountReference, $remarks);
}
```

### 3. Top Up Verification

```php
class B2CTopUpVerificationService
{
    public function verifyTopUp($conversationId)
    {
        // Check top-up status in database
        $topUp = B2CTopUp::where('conversation_id', $conversationId)->first();
        
        if (!$topUp) {
            throw new Exception('Top up not found');
        }
        
        // If top-up is still pending, check with M-Pesa
        if ($topUp->status === 'pending') {
            $this->checkTopUpStatusWithMpesa($topUp);
        }
        
        return $topUp;
    }
    
    private function checkTopUpStatusWithMpesa($topUp)
    {
        // Use transaction status API to check top-up
        $mpesa = new Mpesa();
        
        if ($topUp->transaction_id) {
            $response = $mpesa->transactionStatus(
                transactionId: $topUp->transaction_id,
                identifierType: '4',
                remarks: 'B2C top up status check',
                occasion: 'Verification'
            );
            
            // Process status response
            $this->processStatusResponse($topUp, $response);
        }
    }
}
```

## Troubleshooting

### Common Issues

1. **Invalid Initiator Information**
   - Check initiator username and password
   - Verify security credentials are properly encrypted
   - Ensure API user has "Org Business Pay to Bulk API initiator" role

2. **Insufficient Funds**
   - Ensure business account has sufficient balance
   - Check account status and limits
   - Verify shortcode is active

3. **Invalid Receiver Shortcode**
   - Ensure B2C shortcode is valid and active
   - Check shortcode format (5-6 digits)
   - Verify B2C shortcode accepts top-ups

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
Log::channel('b2c_topup')->info('B2C top up request', [
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
    Log::error('B2C top up error: ' . $e->getMessage());
}
```

### Response Validation

```php
public function validateB2CTopUpResponse($response)
{
    if (!isset($response['ResponseCode'])) {
        throw new Exception('Invalid response format');
    }
    
    if ($response['ResponseCode'] !== '0') {
        throw new Exception('B2C top up failed: ' . ($response['ResponseDescription'] ?? 'Unknown error'));
    }
    
    return $response;
}
```

## Testing

### Unit Tests

```php
public function test_b2c_topup_request()
{
    $mpesa = new Mpesa();
    
    $response = $mpesa->b2bPayment(
        'BusinessPayToBulk',
        500000,
        '600000',
        'TEST-TOPUP-001',
        'Test B2C top up'
    );
    
    $this->assertArrayHasKey('ResponseCode', $response);
    $this->assertArrayHasKey('ConversationID', $response);
    $this->assertEquals('0', $response['ResponseCode']);
}
```

### Integration Tests

```php
public function test_b2c_topup_workflow()
{
    $mpesa = new Mpesa();
    
    // Test B2C top up
    $response = $mpesa->b2bPayment(
        'BusinessPayToBulk',
        1000000,
        '600000',
        'TEST-TOPUP-001',
        'Integration test top up'
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

1. **Always validate input data** before making B2C top-up requests
2. **Use appropriate command IDs** (BusinessPayToBulk only)
3. **Implement proper error handling** and logging
4. **Set up callback URLs** to handle asynchronous responses
5. **Test thoroughly** in sandbox environment before production
6. **Monitor B2C top-up success rates** and investigate failures
7. **Implement rate limiting** to prevent abuse
8. **Maintain audit trails** for all B2C top-ups
9. **Keep security credentials secure** and rotate regularly
10. **Regularly reconcile** top-ups with disbursement records
