# M-Pesa Business Buy Goods API

## Overview

The M-Pesa Business Buy Goods API enables businesses to pay for goods and services directly from their business account to a till number, merchant store number, or Merchant HO. This API allows you to pay on behalf of a consumer/requester, moving money from your MMF/Working account to the recipient's merchant account.

## Key Features

- **Direct Business Payments**: Pay for goods and services directly from business accounts
- **Consumer Proxy Payments**: Pay on behalf of consumers/requesters
- **Real-time Processing**: Instant payment processing and confirmation
- **Callback Notifications**: Receive real-time updates on payment status
- **Account References**: Support for customer account identification
- **Secure Transactions**: Encrypted security credentials for secure operations
- **Queue Management**: Handle timeouts and processing delays gracefully
- **Multiple Use Cases**: Retail purchases, service payments, and more

## Prerequisites

### Business Requirements

Before using this API, your organization must:

1. **M-Pesa Business Account**: Have an active M-Pesa business account
2. **API Access**: Obtain API credentials from Safaricom
3. **Initiator Role**: Ensure your API user has "Org Business Buy Goods API initiator" role
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
    'resultURL' => env('MPESA_RESULT_URL', 'https://your-domain.com/mpesa/buy-goods/result'),
    'queueTimeOutURL' => env('MPESA_QUEUE_TIMEOUT_URL', 'https://your-domain.com/mpesa/buy-goods/timeout'),
];
```

### Required Environment Variables

```env
MPESA_ENVIRONMENT=sandbox
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_INITIATOR_USERNAME=your_initiator_username
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_LIPA_NA_MPESA_SHORTCODE=your_shortcode
MPESA_RESULT_URL=https://your-domain.com/mpesa/buy-goods/result
MPESA_QUEUE_TIMEOUT_URL=https://your-domain.com/mpesa/buy-goods/timeout
```

## Usage

### Basic Buy Goods Payment

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

$response = $mpesa->b2bPayment(
    commandId: 'BusinessBuyGoods',
    amount: 50000,
    receiverShortcode: '123456',
    accountReference: 'GOODS-ACC-001',
    remarks: 'Payment for office supplies'
);
```

### Advanced Usage with Error Handling

```php
use Itsmurumba\Mpesa\Mpesa;
use Exception;

try {
    $mpesa = new Mpesa();
    
    $response = $mpesa->b2bPayment(
        commandId: 'BusinessBuyGoods',
        amount: 75000,
        receiverShortcode: '123456',
        accountReference: 'INV-2024-001',
        remarks: 'Payment for office equipment - January 2024'
    );
    
    // Handle successful buy goods payment request
    if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
        $conversationId = $response['ConversationID'];
        $originatorConversationId = $response['OriginatorConversationID'];
        
        // Store buy goods payment request details
        BuyGoodsPayment::create([
            'command_id' => 'BusinessBuyGoods',
            'amount' => 75000,
            'receiver_shortcode' => '123456',
            'account_reference' => 'INV-2024-001',
            'conversation_id' => $conversationId,
            'originator_conversation_id' => $originatorConversationId,
            'status' => 'pending',
            'remarks' => 'Payment for office equipment - January 2024'
        ]);
        
        // Log successful request
        Log::info('Buy goods payment initiated successfully', [
            'amount' => 75000,
            'receiver_shortcode' => '123456',
            'conversation_id' => $conversationId
        ]);
    }
    
} catch (Exception $e) {
    // Handle errors
    Log::error('Buy goods payment failed: ' . $e->getMessage());
    throw $e;
}
```

### Different Buy Goods Payment Scenarios

```php
// Office Supplies Payment
$officeSuppliesPayment = $mpesa->b2bPayment(
    'BusinessBuyGoods',
    45000,
    '123456', // Office supplies merchant
    'SUPPLIES-ACC-001',
    'Office supplies payment - January 2024'
);

// Equipment Purchase
$equipmentPayment = $mpesa->b2bPayment(
    'BusinessBuyGoods',
    120000,
    '789012', // Equipment supplier
    'EQUIP-ACC-001',
    'Computer equipment purchase - Q1 2024'
);

// Service Payment
$servicePayment = $mpesa->b2bPayment(
    'BusinessBuyGoods',
    25000,
    '345678', // Service provider
    'SVC-ACC-001',
    'Cleaning services payment - January 2024'
);

// Inventory Purchase
$inventoryPayment = $mpesa->b2bPayment(
    'BusinessBuyGoods',
    200000,
    '567890', // Inventory supplier
    'INV-ACC-001',
    'Inventory restocking payment - January 2024'
);
```

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `commandId` | string | Yes | For this API use "BusinessBuyGoods" only |
| `amount` | integer | Yes | The transaction amount to be paid |
| `receiverShortcode` | string | Yes | The till number or merchant store number |
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
Route::post('/mpesa/buy-goods/result', function (Request $request) {
    $data = $request->all();
    
    // Verify the callback
    if (isset($data['Result'])) {
        $result = $data['Result'];
        
        switch ($result['ResultCode']) {
            case '0':
                // Buy goods payment successful
                $transactionId = $result['TransactionID'];
                $amount = $this->extractAmount($result['ResultParameters']);
                $receiverPartyName = $this->extractReceiverPartyName($result['ResultParameters']);
                $billReferenceNumber = $this->extractBillReferenceNumber($result['ReferenceData']);
                
                // Update buy goods payment record
                BuyGoodsPayment::where('conversation_id', $result['ConversationID'])
                    ->update([
                        'status' => 'completed',
                        'transaction_id' => $transactionId,
                        'completed_at' => now()
                    ]);
                
                // Send confirmation to recipient
                $this->sendPaymentConfirmation($receiverPartyName, $amount, $transactionId);
                
                Log::info('Buy goods payment completed successfully', [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'receiver_party' => $receiverPartyName
                ]);
                break;
                
            case '2001':
                // Buy goods payment failed
                Log::error('Buy goods payment failed: ' . $result['ResultDesc']);
                break;
                
            default:
                // Other status codes
                Log::warning('Buy goods payment status unknown', $result);
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
Route::post('/mpesa/buy-goods/timeout', function (Request $request) {
    $data = $request->all();
    
    // Handle timeout - check transaction status manually
    Log::warning('Buy goods payment request timed out', $data);
    
    // You may want to check the transaction status manually
    // or implement retry logic
    
    return response()->json(['status' => 'timeout_handled']);
});
```

## Real-World Usage Examples

### Automated Inventory Purchase System

```php
class AutomatedInventoryPurchaseService
{
    public function processInventoryPurchases()
    {
        $mpesa = new Mpesa();
        
        // Get pending inventory orders
        $inventoryOrders = InventoryOrder::where('status', 'pending')
            ->where('payment_method', 'mpesa_b2b')
            ->get();
        
        foreach ($inventoryOrders as $order) {
            try {
                $response = $mpesa->b2bPayment(
                    commandId: 'BusinessBuyGoods',
                    amount: $order->total_amount,
                    receiverShortcode: $order->supplier->till_number,
                    accountReference: "INV-{$order->id}",
                    remarks: "Inventory purchase - {$order->supplier->name} - {$order->order_number}"
                );
                
                if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
                    // Update order status
                    $order->update([
                        'status' => 'processing',
                        'conversation_id' => $response['ConversationID'],
                        'payment_initiated_at' => now()
                    ]);
                    
                    // Send notification
                    $this->notifyInventoryPurchaseInitiated($order);
                }
                
            } catch (Exception $e) {
                Log::error("Inventory purchase failed for order {$order->id}: " . $e->getMessage());
                
                // Mark for retry
                $order->update([
                    'status' => 'payment_failed',
                    'error_message' => $e->getMessage()
                ]);
            }
        }
    }
    
    private function notifyInventoryPurchaseInitiated($order)
    {
        $message = "Inventory purchase payment of KES {$order->total_amount} has been initiated. Order: {$order->order_number}";
        
        // Send email notification
        Mail::to($order->business->email)->send(new InventoryPurchaseInitiatedMail($order));
        
        // Send SMS notification
        SMS::send($order->business->phone, $message);
    }
}
```

### Office Supplies Management System

```php
class OfficeSuppliesPaymentService
{
    public function processSuppliesPayment($supplier, $items, $totalAmount)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->b2bPayment(
            commandId: 'BusinessBuyGoods',
            amount: $totalAmount,
            receiverShortcode: $supplier->till_number,
            accountReference: "SUPPLIES-{$supplier->id}-" . date('Ymd'),
            remarks: "Office supplies payment - {$supplier->name} - " . count($items) . " items"
        );
        
        if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
            // Create supplies payment record
            SuppliesPayment::create([
                'supplier_id' => $supplier->id,
                'amount' => $totalAmount,
                'items_count' => count($items),
                'conversation_id' => $response['ConversationID'],
                'status' => 'processing'
            ]);
            
            // Update supplier payment status
            $supplier->update(['last_payment_date' => now()]);
        }
        
        return $response;
    }
    
    public function handleSuppliesPaymentCallback($conversationId, $resultCode, $transactionId)
    {
        $suppliesPayment = SuppliesPayment::where('conversation_id', $conversationId)->first();
        
        if ($suppliesPayment) {
            if ($resultCode === '0') {
                $suppliesPayment->update([
                    'status' => 'completed',
                    'transaction_id' => $transactionId,
                    'completed_at' => now()
                ]);
                
                // Send receipt to business
                $this->sendSuppliesReceipt($suppliesPayment);
                
                // Update inventory levels
                $this->updateInventoryLevels($suppliesPayment);
            } else {
                $suppliesPayment->update([
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
        
        $response = $mpesa->b2bPayment(
            commandId: 'BusinessBuyGoods',
            amount: $amount,
            receiverShortcode: $supplier->till_number,
            accountReference: "EQUIP-{$equipment->id}-{$supplier->id}",
            remarks: "Equipment purchase - {$equipment->name} from {$supplier->name}"
        );
        
        if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
            // Create equipment purchase record
            EquipmentPurchase::create([
                'equipment_id' => $equipment->id,
                'supplier_id' => $supplier->id,
                'amount' => $amount,
                'conversation_id' => $response['ConversationID'],
                'status' => 'processing'
            ]);
            
            // Update equipment status
            $equipment->update(['purchase_status' => 'payment_processing']);
        }
        
        return $response;
    }
    
    public function handleEquipmentPurchaseCallback($conversationId, $resultCode, $transactionId)
    {
        $equipmentPurchase = EquipmentPurchase::where('conversation_id', $conversationId)->first();
        
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
public function validateBuyGoodsPaymentRequest($commandId, $amount, $receiverShortcode, $accountReference, $remarks)
{
    // Validate command ID
    if ($commandId !== 'BusinessBuyGoods') {
        throw new InvalidArgumentException('Invalid command ID. Use "BusinessBuyGoods" only.');
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

public function processBuyGoodsPaymentWithRateLimit($commandId, $amount, $receiverShortcode, $accountReference, $remarks)
{
    $rateLimiter = app(RateLimiter::class);
    $key = "buy_goods_payment:{$receiverShortcode}";
    
    if ($rateLimiter->tooManyAttempts($key, 20)) { // 20 payments per hour
        throw new Exception('Too many buy goods payment attempts for this receiver');
    }
    
    $rateLimiter->hit($key, 3600); // 1 hour window
    
    return $this->mpesa->b2bPayment($commandId, $amount, $receiverShortcode, $accountReference, $remarks);
}
```

### 3. Payment Verification

```php
class BuyGoodsPaymentVerificationService
{
    public function verifyPayment($conversationId)
    {
        // Check payment status in database
        $payment = BuyGoodsPayment::where('conversation_id', $conversationId)->first();
        
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
                remarks: 'Buy goods payment status check',
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
   - Ensure API user has "Org Business Buy Goods API initiator" role

2. **Insufficient Funds**
   - Ensure business account has sufficient balance
   - Check account status and limits
   - Verify shortcode is active

3. **Invalid Receiver Shortcode**
   - Ensure receiver shortcode is valid and active
   - Check shortcode format (5-6 digits)
   - Verify receiver accepts buy goods payments

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
Log::channel('buy_goods_payment')->info('Buy goods payment request', [
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
    Log::error('Buy goods payment error: ' . $e->getMessage());
}
```

### Response Validation

```php
public function validateBuyGoodsResponse($response)
{
    if (!isset($response['ResponseCode'])) {
        throw new Exception('Invalid response format');
    }
    
    if ($response['ResponseCode'] !== '0') {
        throw new Exception('Buy goods payment failed: ' . ($response['ResponseDescription'] ?? 'Unknown error'));
    }
    
    return $response;
}
```

## Testing

### Unit Tests

```php
public function test_buy_goods_payment_request()
{
    $mpesa = new Mpesa();
    
    $response = $mpesa->b2bPayment(
        'BusinessBuyGoods',
        50000,
        '123456',
        'TEST-ACC-001',
        'Test buy goods payment'
    );
    
    $this->assertArrayHasKey('ResponseCode', $response);
    $this->assertArrayHasKey('ConversationID', $response);
    $this->assertEquals('0', $response['ResponseCode']);
}
```

### Integration Tests

```php
public function test_buy_goods_payment_workflow()
{
    $mpesa = new Mpesa();
    
    // Test buy goods payment
    $response = $mpesa->b2bPayment(
        'BusinessBuyGoods',
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

1. **Always validate input data** before making buy goods payment requests
2. **Use appropriate command IDs** (BusinessBuyGoods only)
3. **Implement proper error handling** and logging
4. **Set up callback URLs** to handle asynchronous responses
5. **Test thoroughly** in sandbox environment before production
6. **Monitor buy goods payment success rates** and investigate failures
7. **Implement rate limiting** to prevent abuse
8. **Maintain audit trails** for all buy goods payments
9. **Keep security credentials secure** and rotate regularly
10. **Regularly reconcile** payments with supplier records
