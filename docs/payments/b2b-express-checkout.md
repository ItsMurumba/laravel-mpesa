# M-Pesa B2B Express Checkout API

## Overview

The M-Pesa B2B Express Checkout (USSD Push to Till) API enables merchants to initiate USSD Push to till, allowing their fellow merchants to pay from their own till numbers to the vendor's paybill. This is a product for enabling merchants to initiate USSD Push to Till, enabling their fellow merchants to pay from their owned till numbers to the vendor's paybill.

## Key Features

- **USSD Push to Till**: Initiate USSD push to merchant till numbers
- **Merchant-to-Merchant Payments**: Enable payments between merchants
- **Real-time Processing**: Instant USSD push and payment processing
- **Callback Notifications**: Receive real-time updates on payment status
- **Operator Authentication**: Secure operator ID and PIN authentication
- **Payment References**: Support for payment reference tracking
- **Multiple Operators**: Support for multiple operators per organization
- **Organization Management**: Link operators to phone numbers for notifications

## Prerequisites

### Business Requirements

Before using this API, your organization must:

1. **M-Pesa Business Account**: Have an active M-Pesa business account
2. **API Access**: Obtain API credentials from Safaricom
3. **Daraja Product Access**: Add the B2B Express Checkout product to your app
4. **Consumer App Key/Secret**: Obtain consumer appKey and consumer appSecret
5. **Valid Shortcode**: Have a registered business shortcode/till number
6. **Operator Setup**: Configure operators in M-Pesa Web Portal

### Required Permissions

- **Consumer App Key**: Daraja app consumer key
- **Consumer App Secret**: Daraja app consumer secret
- **Organization Operators**: Configured operators with proper roles
- **Nominated Number**: Operator phone number configured in M-Pesa Web Portal

## Configuration

### Environment Setup

```php
// config/mpesa.php
return [
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'), // sandbox or production
    'baseUrl' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
    'consumerKey' => env('MPESA_CONSUMER_KEY', ''),
    'consumerSecret' => env('MPESA_CONSUMER_SECRET', ''),
    'resultURL' => env('MPESA_RESULT_URL', 'https://your-domain.com/mpesa/b2b-express/result'),
    'queueTimeOutURL' => env('MPESA_QUEUE_TIMEOUT_URL', 'https://your-domain.com/mpesa/b2b-express/timeout'),
];
```

### Required Environment Variables

```env
MPESA_ENVIRONMENT=sandbox
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_CONSUMER_KEY=your_consumer_app_key
MPESA_CONSUMER_SECRET=your_consumer_app_secret
MPESA_RESULT_URL=https://your-domain.com/mpesa/b2b-express/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/b2b-express/timeout
```

## Usage

### Basic B2B Express Checkout

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

$response = $mpesa->b2bExpressCheckout(
    primaryShortCode: '000001',
    receiverShortCode: '000002',
    amount: '100',
    paymentRef: 'INV-2024-001',
    callbackUrl: 'https://your-domain.com/mpesa/b2b-express/result',
    partnerName: 'Vendor Company'
);
```

### Advanced Usage with Error Handling

```php
use Itsmurumba\Mpesa\Mpesa;
use Exception;

try {
    $mpesa = new Mpesa();
    
    $response = $mpesa->b2bExpressCheckout(
        primaryShortCode: '000001',
        receiverShortCode: '000002',
        amount: '50000',
        paymentRef: 'INV-2024-001',
        callbackUrl: 'https://your-domain.com/mpesa/b2b-express/result',
        partnerName: 'Office Supplies Ltd',
        requestRefId: '550e8400-e29b-41d4-a716-446655440000'
    );
    
    // Handle successful USSD push initiation
    if (isset($response['code']) && $response['code'] === '0') {
        $requestId = $response['requestId'] ?? null;
        
        // Store B2B Express Checkout request details
        B2BExpressCheckout::create([
            'primary_short_code' => '000001',
            'receiver_short_code' => '000002',
            'amount' => 50000,
            'payment_ref' => 'INV-2024-001',
            'partner_name' => 'Office Supplies Ltd',
            'request_ref_id' => '550e8400-e29b-41d4-a716-446655440000',
            'status' => 'initiated',
            'callback_url' => 'https://your-domain.com/mpesa/b2b-express/result'
        ]);
        
        // Log successful request
        Log::info('B2B Express Checkout initiated successfully', [
            'amount' => 50000,
            'receiver_short_code' => '000002',
            'request_ref_id' => '550e8400-e29b-41d4-a716-446655440000'
        ]);
    }
    
} catch (Exception $e) {
    // Handle errors
    Log::error('B2B Express Checkout failed: ' . $e->getMessage());
    throw $e;
}
```

### Different B2B Express Checkout Scenarios

```php
// Supplier Payment
$supplierPayment = $mpesa->b2bExpressCheckout(
    '000001', // Merchant till
    '000002', // Supplier paybill
    '75000',
    'SUPPLY-2024-001',
    'https://your-domain.com/mpesa/b2b-express/result',
    'ABC Suppliers Ltd'
);

// Service Provider Payment
$servicePayment = $mpesa->b2bExpressCheckout(
    '000001', // Merchant till
    '000003', // Service provider paybill
    '25000',
    'SERVICE-2024-001',
    'https://your-domain.com/mpesa/b2b-express/result',
    'Tech Solutions Ltd'
);

// Equipment Purchase
$equipmentPayment = $mpesa->b2bExpressCheckout(
    '000001', // Merchant till
    '000004', // Equipment supplier paybill
    '150000',
    'EQUIP-2024-001',
    'https://your-domain.com/mpesa/b2b-express/result',
    'Office Equipment Co'
);

// Utility Payment
$utilityPayment = $mpesa->b2bExpressCheckout(
    '000001', // Merchant till
    '000005', // Utility company paybill
    '45000',
    'UTIL-2024-001',
    'https://your-domain.com/mpesa/b2b-express/result',
    'Power Company Ltd'
);
```

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `primaryShortCode` | string | Yes | The debit party, the merchant's till (organization sending money) shortCode/tillNumber |
| `receiverShortCode` | string | Yes | The credit party, the vendor (payBill Account) receiving the amount from the merchant |
| `amount` | string | Yes | Amount to be sent to vendor |
| `paymentRef` | string | Yes | Reference to the payment being made (appears in the text for easy reference by the merchant) |
| `callbackUrl` | string | Yes | The endpoint from the vendor system that will be used to send back the confirmation response |
| `partnerName` | string | Yes | The organization friendly name used by the vendor as known by the Merchant |
| `requestRefId` | string | No | Random unique identifier sent by the vendor system for tracking the process |

## Response Format

### Success Response

```json
{
    "code": "0",
    "status": "USSD Initiated Successfully"
}
```

### Error Response

```json
{
    "code": "4001",
    "status": "USSD Initiation Failed",
    "message": "Invalid parameters provided"
}
```

## Callback Handling

### USSD Callback Response

```php
// routes/web.php
Route::post('/mpesa/b2b-express/result', function (Request $request) {
    $data = $request->all();
    
    // Handle USSD callback response
    if (isset($data['resultCode'])) {
        switch ($data['resultCode']) {
            case '0':
                // USSD push successful
                $transactionId = $data['transactionId'] ?? null;
                $amount = $data['amount'] ?? null;
                $requestId = $data['requestId'] ?? null;
                $conversationId = $data['conversationID'] ?? null;
                
                // Update B2B Express Checkout record
                B2BExpressCheckout::where('request_ref_id', $requestId)
                    ->update([
                        'status' => 'completed',
                        'transaction_id' => $transactionId,
                        'conversation_id' => $conversationId,
                        'completed_at' => now()
                    ]);
                
                // Send confirmation to vendor
                $this->sendPaymentConfirmation($amount, $transactionId, $requestId);
                
                Log::info('B2B Express Checkout completed successfully', [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'request_id' => $requestId
                ]);
                break;
                
            case '4001':
                // USSD push cancelled by user
                $requestId = $data['requestId'] ?? null;
                $amount = $data['amount'] ?? null;
                
                // Update B2B Express Checkout record
                B2BExpressCheckout::where('request_ref_id', $requestId)
                    ->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now()
                    ]);
                
                Log::warning('B2B Express Checkout cancelled by user', [
                    'request_id' => $requestId,
                    'amount' => $amount
                ]);
                break;
                
            default:
                // Other status codes
                Log::warning('B2B Express Checkout status unknown', $data);
        }
    }
    
    return response()->json(['status' => 'received']);
});

private function sendPaymentConfirmation($amount, $transactionId, $requestId)
{
    // Send email confirmation to vendor
    $vendor = B2BExpressCheckout::where('request_ref_id', $requestId)->first();
    
    if ($vendor) {
        Mail::to($vendor->email)->send(new B2BExpressPaymentConfirmationMail([
            'amount' => $amount,
            'transaction_id' => $transactionId,
            'request_id' => $requestId
        ]));
    }
}
```

### Queue Timeout URL Callback

```php
Route::post('/mpesa/b2b-express/timeout', function (Request $request) {
    $data = $request->all();
    
    // Handle timeout - check transaction status manually
    Log::warning('B2B Express Checkout request timed out', $data);
    
    // You may want to check the transaction status manually
    // or implement retry logic
    
    return response()->json(['status' => 'timeout_handled']);
});
```

## Real-World Usage Examples

### Automated Supplier Payment System

```php
class AutomatedSupplierPaymentService
{
    public function processSupplierPayments()
    {
        $mpesa = new Mpesa();
        
        // Get pending supplier payments
        $supplierPayments = SupplierPayment::where('status', 'pending')
            ->where('payment_method', 'b2b_express')
            ->get();
        
        foreach ($supplierPayments as $payment) {
            try {
                $response = $mpesa->b2bExpressCheckout(
                    primaryShortCode: $payment->merchant->till_number,
                    receiverShortCode: $payment->supplier->paybill_number,
                    amount: $payment->amount,
                    paymentRef: "SUPPLY-{$payment->id}",
                    callbackUrl: 'https://your-domain.com/mpesa/b2b-express/result',
                    partnerName: $payment->supplier->name,
                    requestRefId: $this->generateUniqueRequestId()
                );
                
                if (isset($response['code']) && $response['code'] === '0') {
                    // Update payment status
                    $payment->update([
                        'status' => 'processing',
                        'request_ref_id' => $response['requestId'] ?? null,
                        'initiated_at' => now()
                    ]);
                    
                    // Send notification
                    $this->notifySupplierPaymentInitiated($payment);
                }
                
            } catch (Exception $e) {
                Log::error("Supplier payment failed for payment {$payment->id}: " . $e->getMessage());
                
                // Mark for retry
                $payment->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }
    
    private function notifySupplierPaymentInitiated($payment)
    {
        $message = "Supplier payment of KES {$payment->amount} has been initiated. Payment ID: {$payment->id}";
        
        // Send email notification
        Mail::to($payment->merchant->email)->send(new SupplierPaymentInitiatedMail($payment));
        
        // Send SMS notification
        SMS::send($payment->merchant->phone, $message);
    }
    
    private function generateUniqueRequestId()
    {
        return uniqid('supplier_payment_', true);
    }
}
```

### Service Provider Payment System

```php
class ServiceProviderPaymentService
{
    public function processServicePayment($serviceProvider, $amount, $serviceType)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->b2bExpressCheckout(
            primaryShortCode: $this->getMerchantTillNumber(),
            receiverShortCode: $serviceProvider->paybill_number,
            amount: $amount,
            paymentRef: "SERVICE-{$serviceType}-" . date('Ymd'),
            callbackUrl: 'https://your-domain.com/mpesa/b2b-express/result',
            partnerName: $serviceProvider->name,
            requestRefId: $this->generateRequestId()
        );
        
        if (isset($response['code']) && $response['code'] === '0') {
            // Create service payment record
            ServicePayment::create([
                'service_provider_id' => $serviceProvider->id,
                'amount' => $amount,
                'service_type' => $serviceType,
                'request_ref_id' => $response['requestId'] ?? null,
                'status' => 'processing'
            ]);
            
            // Update service provider payment status
            $serviceProvider->update(['last_payment_date' => now()]);
        }
        
        return $response;
    }
    
    public function handleServicePaymentCallback($requestId, $resultCode, $transactionId)
    {
        $servicePayment = ServicePayment::where('request_ref_id', $requestId)->first();
        
        if ($servicePayment) {
            if ($resultCode === '0') {
                $servicePayment->update([
                    'status' => 'completed',
                    'transaction_id' => $transactionId,
                    'completed_at' => now()
                ]);
                
                // Send receipt to merchant
                $this->sendServiceReceipt($servicePayment);
                
                // Update service records
                $this->updateServiceRecords($servicePayment);
            } else {
                $servicePayment->update([
                    'status' => 'failed',
                    'error_message' => 'Payment failed'
                ]);
            }
        }
    }
}
```

### Equipment Purchase System

```php
class EquipmentPurchaseService
{
    public function processEquipmentPurchase($equipment, $supplier, $amount)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->b2bExpressCheckout(
            primaryShortCode: $this->getMerchantTillNumber(),
            receiverShortCode: $supplier->paybill_number,
            amount: $amount,
            paymentRef: "EQUIP-{$equipment->id}-{$supplier->id}",
            callbackUrl: 'https://your-domain.com/mpesa/b2b-express/result',
            partnerName: $supplier->name,
            requestRefId: $this->generateRequestId()
        );
        
        if (isset($response['code']) && $response['code'] === '0') {
            // Create equipment purchase record
            EquipmentPurchase::create([
                'equipment_id' => $equipment->id,
                'supplier_id' => $supplier->id,
                'amount' => $amount,
                'request_ref_id' => $response['requestId'] ?? null,
                'status' => 'processing'
            ]);
            
            // Update equipment status
            $equipment->update(['purchase_status' => 'payment_processing']);
        }
        
        return $response;
    }
    
    public function handleEquipmentPurchaseCallback($requestId, $resultCode, $transactionId)
    {
        $equipmentPurchase = EquipmentPurchase::where('request_ref_id', $requestId)->first();
        
        if ($equipmentPurchase) {
            if ($resultCode === '0') {
                $equipmentPurchase->update([
                    'status' => 'completed',
                    'transaction_id' => $transactionId,
                    'completed_at' => now()
                ]);
                
                // Update equipment status
                $equipmentPurchase->equipment->update([
                    'purchase_status' => 'completed',
                    'purchase_date' => now()
                ]);
                
                // Send purchase confirmation
                $this->sendEquipmentPurchaseConfirmation($equipmentPurchase);
            } else {
                $equipmentPurchase->update([
                    'status' => 'failed',
                    'error_message' => 'Payment failed'
                ]);
                
                // Reset equipment status
                $equipmentPurchase->equipment->update(['purchase_status' => 'pending']);
            }
        }
    }
}
```

## Security Best Practices

### 1. Validate Input Data

```php
public function validateB2BExpressCheckoutRequest($primaryShortCode, $receiverShortCode, $amount, $paymentRef, $callbackUrl, $partnerName)
{
    // Validate primary short code
    if (!preg_match('/^[0-9]{5,6}$/', $primaryShortCode)) {
        throw new InvalidArgumentException('Invalid primary short code format');
    }
    
    // Validate receiver short code
    if (!preg_match('/^[0-9]{5,6}$/', $receiverShortCode)) {
        throw new InvalidArgumentException('Invalid receiver short code format');
    }
    
    // Validate amount
    if ($amount <= 0 || $amount > 10000000) { // 10M KES limit
        throw new InvalidArgumentException('Invalid amount');
    }
    
    // Validate payment reference
    if (strlen($paymentRef) > 50) {
        throw new InvalidArgumentException('Payment reference too long (max 50 characters)');
    }
    
    // Validate callback URL
    if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('Invalid callback URL format');
    }
    
    // Validate partner name
    if (strlen($partnerName) > 100) {
        throw new InvalidArgumentException('Partner name too long (max 100 characters)');
    }
    
    return true;
}
```

### 2. Implement Rate Limiting

```php
use Illuminate\Cache\RateLimiter;

public function processB2BExpressCheckoutWithRateLimit($primaryShortCode, $receiverShortCode, $amount, $paymentRef, $callbackUrl, $partnerName)
{
    $rateLimiter = app(RateLimiter::class);
    $key = "b2b_express_checkout:{$primaryShortCode}";
    
    if ($rateLimiter->tooManyAttempts($key, 10)) { // 10 requests per hour
        throw new Exception('Too many B2B Express Checkout attempts for this merchant');
    }
    
    $rateLimiter->hit($key, 3600); // 1 hour window
    
    return $this->mpesa->b2bExpressCheckout($primaryShortCode, $receiverShortCode, $amount, $paymentRef, $callbackUrl, $partnerName);
}
```

### 3. Payment Verification

```php
class B2BExpressCheckoutVerificationService
{
    public function verifyPayment($requestRefId)
    {
        // Check payment status in database
        $payment = B2BExpressCheckout::where('request_ref_id', $requestRefId)->first();
        
        if (!$payment) {
            throw new Exception('Payment not found');
        }
        
        // If payment is still pending, check with M-Pesa
        if ($payment->status === 'initiated') {
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
                remarks: 'B2B Express Checkout status check',
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

1. **Invalid Shortcode Format**
   - Ensure shortcodes are 5-6 digits
   - Verify shortcodes are active and registered
   - Check shortcode ownership and permissions

2. **Operator Authentication Issues**
   - Ensure operator is configured in M-Pesa Web Portal
   - Verify operator phone number is nominated number
   - Check operator has proper roles and permissions

3. **USSD Push Failures**
   - Check merchant till number is active
   - Verify merchant has sufficient balance
   - Ensure operator is available for authentication

4. **Callback URL Issues**
   - Ensure callback URLs are accessible
   - Check URL format and SSL certificates
   - Verify callback handling logic

5. **Request Reference ID Issues**
   - Ensure unique request reference IDs
   - Check for duplicate request IDs
   - Verify request ID format and length

### Debug Mode

```php
// Enable debug logging
Log::channel('b2b_express_checkout')->info('B2B Express Checkout request', [
    'primary_short_code' => $primaryShortCode,
    'receiver_short_code' => $receiverShortCode,
    'amount' => $amount,
    'payment_ref' => $paymentRef,
    'partner_name' => $partnerName,
    'timestamp' => now()
]);
```

## Package Features

### Automatic Configuration

The package automatically handles:
- Environment-specific base URLs
- Access token management
- Request/response formatting
- Request reference ID generation

### Error Handling

```php
try {
    $response = $mpesa->b2bExpressCheckout($primaryShortCode, $receiverShortCode, $amount, $paymentRef, $callbackUrl, $partnerName);
} catch (IsNullException $e) {
    // Handle missing configuration
    Log::error('M-Pesa configuration missing: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other errors
    Log::error('B2B Express Checkout error: ' . $e->getMessage());
}
```

### Response Validation

```php
public function validateB2BExpressCheckoutResponse($response)
{
    if (!isset($response['code'])) {
        throw new Exception('Invalid response format');
    }
    
    if ($response['code'] !== '0') {
        throw new Exception('B2B Express Checkout failed: ' . ($response['status'] ?? 'Unknown error'));
    }
    
    return $response;
}
```

## Testing

### Unit Tests

```php
public function test_b2b_express_checkout_request()
{
    $mpesa = new Mpesa();
    
    $response = $mpesa->b2bExpressCheckout(
        '000001',
        '000002',
        '50000',
        'TEST-ACC-001',
        'https://your-domain.com/mpesa/b2b-express/result',
        'Test Vendor'
    );
    
    $this->assertArrayHasKey('code', $response);
    $this->assertEquals('0', $response['code']);
}
```

### Integration Tests

```php
public function test_b2b_express_checkout_workflow()
{
    $mpesa = new Mpesa();
    
    // Test B2B Express Checkout
    $response = $mpesa->b2bExpressCheckout(
        '000001',
        '000002',
        '100000',
        'TEST-ACC-001',
        'https://your-domain.com/mpesa/b2b-express/result',
        'Test Vendor'
    );
    
    $this->assertEquals('0', $response['code']);
    
    // Test callback handling
    if (isset($response['requestId'])) {
        // Wait for callback or check status manually
        $this->assertNotEmpty($response['requestId']);
    }
}
```

## Best Practices Summary

1. **Always validate input data** before making B2B Express Checkout requests
2. **Use unique request reference IDs** for tracking
3. **Implement proper error handling** and logging
4. **Set up callback URLs** to handle asynchronous responses
5. **Test thoroughly** in sandbox environment before production
6. **Monitor B2B Express Checkout success rates** and investigate failures
7. **Implement rate limiting** to prevent abuse
8. **Maintain audit trails** for all B2B Express Checkout transactions
9. **Keep API credentials secure** and rotate regularly
10. **Regularly reconcile** payments with vendor records
