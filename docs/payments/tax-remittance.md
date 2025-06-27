# M-Pesa Tax Remittance API

## Overview

The M-Pesa Tax Remittance API enables businesses to remit tax directly to the Kenya Revenue Authority (KRA) through the M-Pesa platform. This API facilitates seamless tax payments, allowing organizations to fulfill their tax obligations electronically with real-time processing and confirmation.

## Key Features

- **Direct KRA Integration**: Remit tax directly to Kenya Revenue Authority
- **PRN Support**: Use Payment Registration Numbers (PRN) issued by KRA
- **Real-time Processing**: Instant tax payment processing and confirmation
- **Secure Transactions**: Encrypted security credentials for secure operations
- **Callback Notifications**: Receive real-time updates on tax remittance status
- **Queue Management**: Handle timeouts and processing delays gracefully
- **Compliance Ready**: Meets KRA tax payment requirements

## Prerequisites

### KRA Integration Requirements

Before using this API, your organization must:

1. **Register with KRA**: Complete business registration with Kenya Revenue Authority
2. **Obtain PRN**: Get Payment Registration Numbers for different tax types
3. **API Integration**: Complete technical integration with KRA systems
4. **Tax Declaration**: Set up tax declaration and payment registration processes
5. **M-Pesa Business Account**: Have an active M-Pesa business account with sufficient funds

### Required KRA Information

- **KRA PIN**: Your business KRA Personal Identification Number
- **PRN Numbers**: Payment Registration Numbers for different tax obligations
- **Tax Types**: VAT, Income Tax, Withholding Tax, etc.
- **Payment Periods**: Monthly, quarterly, or annual tax periods

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
    'resultURL' => env('MPESA_RESULT_URL', 'https://your-domain.com/mpesa/tax-remittance/result'),
    'queueTimeOutURL' => env('MPESA_QUEUE_TIMEOUT_URL', 'https://your-domain.com/mpesa/tax-remittance/timeout'),
];
```

### Required Environment Variables

```env
MPESA_ENVIRONMENT=sandbox
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_INITIATOR_USERNAME=your_initiator_username
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_LIPA_NA_MPESA_SHORTCODE=your_shortcode
MPESA_RESULT_URL=https://your-domain.com/mpesa/tax-remittance/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/tax-remittance/timeout
```

## Usage

### Basic Tax Remittance

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

$response = $mpesa->taxRemittance(
    amount: 50000,
    accountReference: 'PRN123456789',
    remarks: 'VAT Payment for January 2024'
);
```

### Advanced Usage with Error Handling

```php
use Itsmurumba\Mpesa\Mpesa;
use Exception;

try {
    $mpesa = new Mpesa();
    
    $response = $mpesa->taxRemittance(
        amount: 75000,
        accountReference: 'PRN987654321',
        remarks: 'Income Tax Payment - Q1 2024'
    );
    
    // Handle successful tax remittance request
    if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
        $conversationId = $response['ConversationID'];
        $originatorConversationId = $response['OriginatorConversationID'];
        
        // Store tax remittance request details
        TaxRemittance::create([
            'amount' => 75000,
            'prn' => 'PRN987654321',
            'conversation_id' => $conversationId,
            'originator_conversation_id' => $originatorConversationId,
            'status' => 'pending',
            'remarks' => 'Income Tax Payment - Q1 2024'
        ]);
        
        // Log successful request
        Log::info('Tax remittance initiated successfully', [
            'amount' => 75000,
            'prn' => 'PRN987654321',
            'conversation_id' => $conversationId
        ]);
    }
    
} catch (Exception $e) {
    // Handle errors
    Log::error('Tax remittance failed: ' . $e->getMessage());
    throw $e;
}
```

### Different Tax Types

```php
// VAT Payment
$vatResponse = $mpesa->taxRemittance(
    45000,
    'PRN-VAT-2024-001',
    'VAT Payment for January 2024'
);

// Income Tax Payment
$incomeTaxResponse = $mpesa->taxRemittance(
    120000,
    'PRN-IT-2024-Q1',
    'Income Tax Payment - Q1 2024'
);

// Withholding Tax Payment
$withholdingTaxResponse = $mpesa->taxRemittance(
    25000,
    'PRN-WHT-2024-001',
    'Withholding Tax Payment - January 2024'
);

// Corporate Tax Payment
$corporateTaxResponse = $mpesa->taxRemittance(
    500000,
    'PRN-CT-2024-001',
    'Corporate Tax Payment - FY 2023/2024'
);
```

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `amount` | string | Yes | The transaction amount to be remitted to KRA |
| `accountReference` | string | Yes | The payment registration number (PRN) issued by KRA |
| `remarks` | string | No | Any additional information (max 100 characters, default: 'Tax Remittance to KRA') |

### Fixed Parameters (Set by API)

| Parameter | Value | Description |
|-----------|-------|-------------|
| `CommandID` | 'PayTaxToKRA' | Specifies tax remittance transaction type |
| `Initiator` | From config | M-Pesa API operator username |
| `SecurityCredential` | Encrypted | Encrypted initiator password |
| `SenderIdentifierType` | '4' | Type of sender shortcode (fixed) |
| `RecieverIdentifierType` | '4' | Type of receiver shortcode (fixed) |
| `PartyA` | From config | Your business shortcode |
| `PartyB` | '572572' | KRA shortcode (fixed) |

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
Route::post('/mpesa/tax-remittance/result', function (Request $request) {
    $data = $request->all();
    
    // Verify the callback
    if (isset($data['Result'])) {
        $result = $data['Result'];
        
        switch ($result['ResultCode']) {
            case '0':
                // Tax remittance successful
                $transactionId = $result['TransactionID'];
                $amount = $this->extractAmount($result['ResultParameters']);
                $prn = $this->extractPRN($result['ReferenceData']);
                
                // Update tax remittance record
                TaxRemittance::where('conversation_id', $result['ConversationID'])
                    ->update([
                        'status' => 'completed',
                        'transaction_id' => $transactionId,
                        'completed_at' => now()
                    ]);
                
                // Send confirmation to KRA
                $this->notifyKRA($prn, $amount, $transactionId);
                
                Log::info('Tax remittance completed successfully', [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'prn' => $prn
                ]);
                break;
                
            case '2001':
                // Tax remittance failed
                Log::error('Tax remittance failed: ' . $result['ResultDesc']);
                break;
                
            default:
                // Other status codes
                Log::warning('Tax remittance status unknown', $result);
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

private function extractPRN($referenceData)
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
Route::post('/mpesa/tax-remittance/timeout', function (Request $request) {
    $data = $request->all();
    
    // Handle timeout - check transaction status manually
    Log::warning('Tax remittance request timed out', $data);
    
    // You may want to check the transaction status manually
    // or implement retry logic
    
    return response()->json(['status' => 'timeout_handled']);
});
```

## Real-World Usage Examples

### Automated Tax Payment System

```php
class AutomatedTaxPaymentService
{
    public function processMonthlyTaxPayments()
    {
        $mpesa = new Mpesa();
        
        // Get pending tax obligations
        $taxObligations = TaxObligation::where('status', 'pending')
            ->where('due_date', '<=', now())
            ->get();
        
        foreach ($taxObligations as $obligation) {
            try {
                $response = $mpesa->taxRemittance(
                    amount: $obligation->amount,
                    accountReference: $obligation->prn,
                    remarks: "{$obligation->tax_type} Payment - {$obligation->period}"
                );
                
                if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
                    // Update obligation status
                    $obligation->update([
                        'status' => 'processing',
                        'conversation_id' => $response['ConversationID'],
                        'initiated_at' => now()
                    ]);
                    
                    // Send notification
                    $this->notifyTaxPaymentInitiated($obligation);
                }
                
            } catch (Exception $e) {
                Log::error("Tax payment failed for obligation {$obligation->id}: " . $e->getMessage());
                
                // Mark for retry
                $obligation->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }
    
    private function notifyTaxPaymentInitiated($obligation)
    {
        $message = "Tax payment of KES {$obligation->amount} for {$obligation->tax_type} has been initiated. PRN: {$obligation->prn}";
        
        // Send email notification
        Mail::to($obligation->business->email)->send(new TaxPaymentInitiatedMail($obligation));
        
        // Send SMS notification
        SMS::send($obligation->business->phone, $message);
    }
}
```

### VAT Payment Integration

```php
class VATPaymentService
{
    public function processVATPayment($business, $period, $amount)
    {
        $mpesa = new Mpesa();
        
        // Generate PRN from KRA
        $prn = $this->generatePRN($business, 'VAT', $period);
        
        try {
            $response = $mpesa->taxRemittance(
                amount: $amount,
                accountReference: $prn,
                remarks: "VAT Payment for {$period}"
            );
            
            if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
                // Create VAT payment record
                VATPayment::create([
                    'business_id' => $business->id,
                    'period' => $period,
                    'amount' => $amount,
                    'prn' => $prn,
                    'conversation_id' => $response['ConversationID'],
                    'status' => 'processing'
                ]);
                
                // Update business VAT status
                $business->vat_payments()->create([
                    'period' => $period,
                    'amount' => $amount,
                    'status' => 'processing'
                ]);
            }
            
        } catch (Exception $e) {
            Log::error("VAT payment failed for business {$business->id}: " . $e->getMessage());
            throw $e;
        }
        
        return $response;
    }
    
    private function generatePRN($business, $taxType, $period)
    {
        // Integrate with KRA API to generate PRN
        // This is a placeholder for KRA integration
        return "PRN-{$taxType}-{$business->kra_pin}-{$period}";
    }
}
```

### Tax Reconciliation Service

```php
class TaxReconciliationService
{
    public function reconcileTaxPayments()
    {
        $mpesa = new Mpesa();
        
        // Get pending tax remittances
        $pendingRemittances = TaxRemittance::where('status', 'processing')
            ->where('created_at', '<=', now()->subHours(2))
            ->get();
        
        foreach ($pendingRemittances as $remittance) {
            try {
                // Check transaction status
                $statusResponse = $mpesa->transactionStatus(
                    transactionId: $remittance->transaction_id ?? 'N/A',
                    identifierType: '4',
                    remarks: 'Tax remittance status check',
                    occasion: 'Reconciliation'
                );
                
                // Process status response
                $this->processStatusResponse($remittance, $statusResponse);
                
            } catch (Exception $e) {
                Log::error("Reconciliation failed for remittance {$remittance->id}: " . $e->getMessage());
            }
        }
    }
    
    private function processStatusResponse($remittance, $response)
    {
        if (isset($response['Result'])) {
            $result = $response['Result'];
            
            if ($result['ResultCode'] === '0') {
                // Payment successful
                $remittance->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
                
                // Notify KRA of successful payment
                $this->notifyKRASuccess($remittance);
                
            } else {
                // Payment failed
                $remittance->update([
                    'status' => 'failed',
                    'error_message' => $result['ResultDesc']
                ]);
                
                // Notify business of failure
                $this->notifyBusinessFailure($remittance);
            }
        }
    }
}
```

## Security Best Practices

### 1. Validate Input Data

```php
public function validateTaxRemittanceRequest($amount, $accountReference, $remarks)
{
    // Validate amount
    if ($amount <= 0 || $amount > 10000000) { // 10M KES limit
        throw new InvalidArgumentException('Invalid amount');
    }
    
    // Validate PRN format
    if (!preg_match('/^PRN-[A-Z0-9-]{10,}$/', $accountReference)) {
        throw new InvalidArgumentException('Invalid PRN format');
    }
    
    // Validate remarks length
    if (strlen($remarks) > 100) {
        throw new InvalidArgumentException('Remarks too long (max 100 characters)');
    }
    
    return true;
}
```

### 2. Implement Rate Limiting

```php
use Illuminate\Cache\RateLimiter;

public function processTaxRemittanceWithRateLimit($amount, $accountReference, $remarks)
{
    $rateLimiter = app(RateLimiter::class);
    $key = "tax_remittance:{$accountReference}";
    
    if ($rateLimiter->tooManyAttempts($key, 3)) { // 3 attempts per day
        throw new Exception('Too many tax remittance attempts for this PRN');
    }
    
    $rateLimiter->hit($key, 86400); // 24 hours
    
    return $this->mpesa->taxRemittance($amount, $accountReference, $remarks);
}
```

### 3. Audit Trail

```php
class TaxRemittanceAuditService
{
    public function logTaxRemittanceRequest($data, $response)
    {
        TaxRemittanceAudit::create([
            'amount' => $data['amount'],
            'prn' => $data['accountReference'],
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

1. **Invalid PRN**
   - Ensure PRN is valid and issued by KRA
   - Check PRN format and expiration
   - Verify PRN is for the correct tax type and period

2. **Insufficient Funds**
   - Ensure business account has sufficient balance
   - Check account status and limits
   - Verify shortcode is active

3. **Authentication Errors**
   - Check initiator username and password
   - Verify security credentials are properly encrypted
   - Ensure API credentials are valid

4. **KRA Integration Issues**
   - Verify KRA integration is complete
   - Check tax declaration status
   - Ensure PRN is properly registered with KRA

5. **Callback URL Issues**
   - Ensure callback URLs are accessible
   - Check URL format and SSL certificates
   - Verify callback handling logic

### Debug Mode

```php
// Enable debug logging
Log::channel('tax_remittance')->info('Tax remittance request', [
    'amount' => $amount,
    'prn' => $accountReference,
    'remarks' => $remarks,
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
    $response = $mpesa->taxRemittance($amount, $accountReference, $remarks);
} catch (IsNullException $e) {
    // Handle missing configuration
    Log::error('M-Pesa configuration missing: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other errors
    Log::error('Tax remittance error: ' . $e->getMessage());
}
```

### Response Validation

```php
public function validateTaxRemittanceResponse($response)
{
    if (!isset($response['ResponseCode'])) {
        throw new Exception('Invalid response format');
    }
    
    if ($response['ResponseCode'] !== '0') {
        throw new Exception('Tax remittance failed: ' . ($response['ResponseDescription'] ?? 'Unknown error'));
    }
    
    return $response;
}
```

## Testing

### Unit Tests

```php
public function test_tax_remittance_request()
{
    $mpesa = new Mpesa();
    
    $response = $mpesa->taxRemittance(
        50000,
        'PRN123456789',
        'Test VAT Payment'
    );
    
    $this->assertArrayHasKey('ResponseCode', $response);
    $this->assertArrayHasKey('ConversationID', $response);
    $this->assertEquals('0', $response['ResponseCode']);
}
```

### Integration Tests

```php
public function test_tax_remittance_workflow()
{
    $mpesa = new Mpesa();
    
    // Test tax remittance
    $response = $mpesa->taxRemittance(
        100000,
        'PRN-TEST-001',
        'Integration test tax payment'
    );
    
    $this->assertEquals('0', $response['ResponseCode']);
    
    // Test transaction status check
    if (isset($response['ConversationID'])) {
        $statusResponse = $mpesa->transactionStatus(
            'N/A', // Transaction ID not available immediately
            '4',
            'Tax remittance status check',
            'Testing'
        );
        
        $this->assertArrayHasKey('Result', $statusResponse);
    }
}
```

## Best Practices Summary

1. **Always validate input data** before making tax remittance requests
2. **Use valid PRNs** issued by KRA for each tax payment
3. **Implement proper error handling** and logging
4. **Set up callback URLs** to handle asynchronous responses
5. **Test thoroughly** in sandbox environment before production
6. **Monitor tax remittance success rates** and investigate failures
7. **Implement rate limiting** to prevent abuse
8. **Maintain audit trails** for all tax payments
9. **Keep KRA integration updated** and compliant
10. **Regularly reconcile** tax payments with KRA records
