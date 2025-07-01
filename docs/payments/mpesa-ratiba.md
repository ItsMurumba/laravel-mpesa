# M-Pesa Ratiba (Standing Order)

## Overview

The M-Pesa Ratiba (Standing Order) API enables businesses to create automatic recurring payments from customers to businesses. This API initiates a standing order request that allows scheduled, automated payments without requiring manual intervention for each transaction.

## Key Features

- **Automatic Recurring Payments**: Set up scheduled payments that execute automatically
- **Flexible Frequency Options**: Support for various payment frequencies (daily, weekly, monthly, etc.)
- **Multiple Transaction Types**: Support for both PayBill and Till Number payments
- **Date Range Control**: Specify start and end dates for standing orders
- **Real-time Processing**: Instant standing order creation and confirmation
- **Callback Notifications**: Receive real-time updates on standing order status
- **Account References**: Support for customer account identification
- **Secure Transactions**: Encrypted security credentials for secure operations
- **Multiple Use Cases**: Rent, subscriptions, loan repayments, and more

## Prerequisites

### Business Requirements

Before using this API, your organization must:

1. **M-Pesa Business Account**: Have an active M-Pesa business account
2. **API Access**: Obtain API credentials from Safaricom
3. **Initiator Role**: Ensure your API user has proper standing order permissions
4. **Valid Shortcode**: Have a registered business shortcode (PayBill or Till Number)
5. **Customer Consent**: Obtain customer consent for recurring payments

### Required Permissions

- **Initiator Username**: M-Pesa API operator username with proper permissions
- **Security Credentials**: Encrypted password for secure authentication
- **Business Shortcode**: Valid 5-6 digit business shortcode
- **Customer Phone Number**: Valid M-Pesa registered phone number

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
    'callBackURL' => env('MPESA_CALLBACK_URL', 'https://your-domain.com/mpesa/ratiba/callback'),
];
```

### Required Environment Variables

```env
MPESA_ENVIRONMENT=sandbox
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_INITIATOR_USERNAME=your_initiator_username
MPESA_INITIATOR_PASSWORD=your_initiator_password
MPESA_LIPA_NA_MPESA_SHORTCODE=your_shortcode
MPESA_CALLBACK_URL=https://your-domain.com/mpesa/ratiba/callback
```

## Usage

### Basic Standing Order Creation

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

$response = $mpesa->createRatibaStandingOrder(
    orderName: 'Monthly Rent Payment',
    startDate: '20240101',
    endDate: '20241231',
    businessShortCode: '123456',
    amount: '50000',
    phoneNumber: '254700000000',
    callBackURL: 'https://your-domain.com/mpesa/ratiba/callback',
    accountReference: 'RENT-001',
    transactionDesc: 'Rent Payment',
    frequency: '4', // Monthly
    transactionType: 'Standing Order Customer Pay Bill',
    receiverPartyIdentifierType: '4' // PayBill
);
```

### Advanced Usage with Error Handling

```php
use Itsmurumba\Mpesa\Mpesa;
use Exception;
use InvalidArgumentException;

try {
    $mpesa = new Mpesa();
    
    $response = $mpesa->createRatibaStandingOrder(
        orderName: 'Quarterly Subscription',
        startDate: '20240101',
        endDate: '20241231',
        businessShortCode: '123456',
        amount: '15000',
        phoneNumber: '254700000000',
        callBackURL: 'https://your-domain.com/mpesa/ratiba/callback',
        accountReference: 'SUB-001',
        transactionDesc: 'Qtrly Sub',
        frequency: '6', // Quarterly
        transactionType: 'Standing Order Customer Pay Bill',
        receiverPartyIdentifierType: '4'
    );
    
    // Handle successful standing order creation
    if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
        $conversationId = $response['ConversationID'];
        $originatorConversationId = $response['OriginatorConversationID'];
        
        // Store standing order details
        StandingOrder::create([
            'order_name' => 'Quarterly Subscription',
            'start_date' => '20240101',
            'end_date' => '20241231',
            'amount' => 15000,
            'phone_number' => '254700000000',
            'account_reference' => 'SUB-001',
            'frequency' => '6',
            'transaction_type' => 'Standing Order Customer Pay Bill',
            'conversation_id' => $conversationId,
            'originator_conversation_id' => $originatorConversationId,
            'status' => 'active'
        ]);
        
        // Log successful creation
        Log::info('Standing order created successfully', [
            'order_name' => 'Quarterly Subscription',
            'amount' => 15000,
            'frequency' => '6',
            'conversation_id' => $conversationId
        ]);
    }
    
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    Log::error('Standing order validation failed: ' . $e->getMessage());
    throw $e;
} catch (Exception $e) {
    // Handle other errors
    Log::error('Standing order creation failed: ' . $e->getMessage());
    throw $e;
}
```

### Different Standing Order Scenarios

```php
// Monthly Rent Payment
$monthlyRent = $mpesa->createRatibaStandingOrder(
    'Monthly Rent Payment',
    '20240101',
    '20241231',
    '123456',
    '75000',
    '254700000000',
    'https://your-domain.com/mpesa/ratiba/callback',
    'RENT-001',
    'Rent Payment',
    '4', // Monthly
    'Standing Order Customer Pay Bill',
    '4' // PayBill
);

// Weekly Loan Repayment
$weeklyLoan = $mpesa->createRatibaStandingOrder(
    'Weekly Loan Repayment',
    '20240101',
    '20241231',
    '123456',
    '5000',
    '254700000000',
    'https://your-domain.com/mpesa/ratiba/callback',
    'LOAN-001',
    'Loan Repayment',
    '3', // Weekly
    'Standing Order Customer Pay Bill',
    '4' // PayBill
);

// Daily Savings
$dailySavings = $mpesa->createRatibaStandingOrder(
    'Daily Savings',
    '20240101',
    '20241231',
    '123456',
    '1000',
    '254700000000',
    'https://your-domain.com/mpesa/ratiba/callback',
    'SAVINGS-001',
    'Daily Savings',
    '2', // Daily
    'Standing Order Customer Pay Bill',
    '4' // PayBill
);

// Quarterly Subscription
$quarterlySub = $mpesa->createRatibaStandingOrder(
    'Quarterly Subscription',
    '20240101',
    '20241231',
    '123456',
    '25000',
    '254700000000',
    'https://your-domain.com/mpesa/ratiba/callback',
    'SUB-001',
    'Qtrly Sub',
    '6', // Quarterly
    'Standing Order Customer Pay Bill',
    '4' // PayBill
);
```

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `orderName` | string | Yes | Unique name for the standing order |
| `startDate` | string | Yes | Start date in YYYYMMDD format |
| `endDate` | string | Yes | End date in YYYYMMDD format |
| `businessShortCode` | string | Yes | Business shortcode for receiving payments |
| `amount` | string | Yes | Amount to be deducted in each transaction (whole numbers only) |
| `phoneNumber` | string | Yes | Customer's phone number in format 254XXXXXXXXX |
| `callBackURL` | string | Yes | URL for callback notifications |
| `accountReference` | string | Yes | Account identifier for PayBill transactions (max 12 chars) |
| `transactionDesc` | string | Yes | Description of the transaction (max 13 chars) |
| `frequency` | string | Yes | Payment frequency (1-8) |
| `transactionType` | string | Yes | Type of standing order transaction |
| `receiverPartyIdentifierType` | string | Yes | "4" for PayBill or "2" for Till Number |

### Frequency Options

| Code | Description |
|------|-------------|
| `1` | One-Off |
| `2` | Daily |
| `3` | Weekly |
| `4` | Monthly |
| `5` | Bi-Monthly |
| `6` | Quarterly |
| `7` | Half-Year |
| `8` | Yearly |

### Transaction Types

| Type | Description |
|------|-------------|
| `Standing Order Customer Pay Bill` | For PayBill payments |
| `Standing Order Customer Pay Marchant` | For Till Number payments |

### Receiver Party Identifier Types

| Type | Description |
|------|-------------|
| `4` | PayBill |
| `2` | Till Number |

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
    "ResponseDescription": "Invalid frequency provided."
}
```

## Callback Handling

### Standing Order Callback

```php
// routes/web.php
Route::post('/mpesa/ratiba/callback', function (Request $request) {
    $data = $request->all();
    
    // Handle standing order callback
    if (isset($data['Result'])) {
        $result = $data['Result'];
        
        switch ($result['ResultCode']) {
            case '0':
                // Standing order successful
                $transactionId = $result['TransactionID'];
                $amount = $this->extractAmount($result['ResultParameters']);
                $standingOrderId = $this->extractStandingOrderId($result['ResultParameters']);
                
                // Update standing order record
                StandingOrder::where('conversation_id', $result['ConversationID'])
                    ->update([
                        'last_payment_date' => now(),
                        'last_transaction_id' => $transactionId,
                        'payment_count' => DB::raw('payment_count + 1')
                    ]);
                
                // Send confirmation to customer
                $this->sendStandingOrderConfirmation($amount, $transactionId);
                
                Log::info('Standing order payment completed successfully', [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'standing_order_id' => $standingOrderId
                ]);
                break;
                
            case '2001':
                // Standing order payment failed
                Log::error('Standing order payment failed: ' . $result['ResultDesc']);
                break;
                
            default:
                // Other status codes
                Log::warning('Standing order status unknown', $result);
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

private function extractStandingOrderId($resultParameters)
{
    foreach ($resultParameters['ResultParameter'] as $param) {
        if ($param['Key'] === 'StandingOrderID') {
            return $param['Value'];
        }
    }
    return null;
}
```

## Real-World Usage Examples

### Automated Rent Collection System

```php
class AutomatedRentCollectionService
{
    public function createRentStandingOrder($tenant, $property, $monthlyRent)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->createRatibaStandingOrder(
            orderName: "Rent Payment - {$property->address}",
            startDate: date('Ymd', strtotime('first day of next month')),
            endDate: date('Ymd', strtotime('last day of this year')),
            businessShortCode: $property->landlord->paybill_number,
            amount: $monthlyRent,
            phoneNumber: $tenant->phone_number,
            callBackURL: 'https://your-domain.com/mpesa/ratiba/callback',
            accountReference: "RENT-{$property->id}-{$tenant->id}",
            transactionDesc: 'Monthly Rent',
            frequency: '4', // Monthly
            transactionType: 'Standing Order Customer Pay Bill',
            receiverPartyIdentifierType: '4'
        );
        
        if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
            // Create rent standing order record
            RentStandingOrder::create([
                'tenant_id' => $tenant->id,
                'property_id' => $property->id,
                'amount' => $monthlyRent,
                'frequency' => '4',
                'start_date' => date('Y-m-d', strtotime('first day of next month')),
                'end_date' => date('Y-m-d', strtotime('last day of this year')),
                'conversation_id' => $response['ConversationID'],
                'status' => 'active'
            ]);
            
            // Send confirmation to tenant
            $this->sendRentStandingOrderConfirmation($tenant, $property, $monthlyRent);
        }
        
        return $response;
    }
    
    public function handleRentPaymentCallback($conversationId, $resultCode, $transactionId)
    {
        $rentStandingOrder = RentStandingOrder::where('conversation_id', $conversationId)->first();
        
        if ($rentStandingOrder) {
            if ($resultCode === '0') {
                $rentStandingOrder->update([
                    'last_payment_date' => now(),
                    'last_transaction_id' => $transactionId,
                    'payment_count' => DB::raw('payment_count + 1')
                ]);
                
                // Create rent payment record
                RentPayment::create([
                    'tenant_id' => $rentStandingOrder->tenant_id,
                    'property_id' => $rentStandingOrder->property_id,
                    'amount' => $rentStandingOrder->amount,
                    'transaction_id' => $transactionId,
                    'payment_date' => now()
                ]);
                
                // Send receipt to tenant
                $this->sendRentReceipt($rentStandingOrder);
            }
        }
    }
}
```

### Loan Repayment System

```php
class LoanRepaymentService
{
    public function createLoanRepaymentStandingOrder($borrower, $loan, $weeklyAmount)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->createRatibaStandingOrder(
            orderName: "Loan Repayment - {$loan->loan_number}",
            startDate: date('Ymd'),
            endDate: date('Ymd', strtotime($loan->maturity_date)),
            businessShortCode: $loan->lender->paybill_number,
            amount: $weeklyAmount,
            phoneNumber: $borrower->phone_number,
            callBackURL: 'https://your-domain.com/mpesa/ratiba/callback',
            accountReference: "LOAN-{$loan->id}",
            transactionDesc: 'Loan Repayment',
            frequency: '3', // Weekly
            transactionType: 'Standing Order Customer Pay Bill',
            receiverPartyIdentifierType: '4'
        );
        
        if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
            // Create loan repayment standing order record
            LoanRepaymentStandingOrder::create([
                'borrower_id' => $borrower->id,
                'loan_id' => $loan->id,
                'amount' => $weeklyAmount,
                'frequency' => '3',
                'start_date' => now(),
                'end_date' => $loan->maturity_date,
                'conversation_id' => $response['ConversationID'],
                'status' => 'active'
            ]);
            
            // Update loan status
            $loan->update(['repayment_method' => 'standing_order']);
        }
        
        return $response;
    }
    
    public function handleLoanRepaymentCallback($conversationId, $resultCode, $transactionId)
    {
        $loanRepaymentOrder = LoanRepaymentStandingOrder::where('conversation_id', $conversationId)->first();
        
        if ($loanRepaymentOrder) {
            if ($resultCode === '0') {
                $loanRepaymentOrder->update([
                    'last_payment_date' => now(),
                    'last_transaction_id' => $transactionId,
                    'payment_count' => DB::raw('payment_count + 1')
                ]);
                
                // Create loan repayment record
                LoanRepayment::create([
                    'loan_id' => $loanRepaymentOrder->loan_id,
                    'borrower_id' => $loanRepaymentOrder->borrower_id,
                    'amount' => $loanRepaymentOrder->amount,
                    'transaction_id' => $transactionId,
                    'payment_date' => now()
                ]);
                
                // Update loan balance
                $this->updateLoanBalance($loanRepaymentOrder->loan_id, $loanRepaymentOrder->amount);
            }
        }
    }
}
```

### Subscription Management System

```php
class SubscriptionManagementService
{
    public function createSubscriptionStandingOrder($customer, $subscription, $amount)
    {
        $mpesa = new Mpesa();
        
        $frequencyMap = [
            'monthly' => '4',
            'quarterly' => '6',
            'yearly' => '8'
        ];
        
        $response = $mpesa->createRatibaStandingOrder(
            orderName: "{$subscription->name} Subscription",
            startDate: date('Ymd'),
            endDate: date('Ymd', strtotime('+1 year')),
            businessShortCode: $subscription->business->paybill_number,
            amount: $amount,
            phoneNumber: $customer->phone_number,
            callBackURL: 'https://your-domain.com/mpesa/ratiba/callback',
            accountReference: "SUB-{$subscription->id}",
            transactionDesc: 'Subscription',
            frequency: $frequencyMap[$subscription->billing_cycle],
            transactionType: 'Standing Order Customer Pay Bill',
            receiverPartyIdentifierType: '4'
        );
        
        if (isset($response['ResponseCode']) && $response['ResponseCode'] === '0') {
            // Create subscription standing order record
            SubscriptionStandingOrder::create([
                'customer_id' => $customer->id,
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'frequency' => $frequencyMap[$subscription->billing_cycle],
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'conversation_id' => $response['ConversationID'],
                'status' => 'active'
            ]);
            
            // Update subscription status
            $subscription->update(['payment_method' => 'standing_order']);
        }
        
        return $response;
    }
}
```

## Security Best Practices

### 1. Validate Input Data

```php
public function validateStandingOrderRequest($orderName, $startDate, $endDate, $amount, $phoneNumber, $frequency, $transactionType, $receiverPartyIdentifierType)
{
    // Validate order name
    if (strlen($orderName) > 100) {
        throw new InvalidArgumentException('Order name too long (max 100 characters)');
    }
    
    // Validate dates
    if (!preg_match('/^\d{8}$/', $startDate) || !preg_match('/^\d{8}$/', $endDate)) {
        throw new InvalidArgumentException('Invalid date format. Use YYYYMMDD');
    }
    
    if (strtotime($startDate) >= strtotime($endDate)) {
        throw new InvalidArgumentException('Start date must be before end date');
    }
    
    // Validate amount
    if ($amount <= 0 || $amount > 10000000) { // 10M KES limit
        throw new InvalidArgumentException('Invalid amount');
    }
    
    // Validate phone number
    if (!preg_match('/^254\d{9}$/', $phoneNumber)) {
        throw new InvalidArgumentException('Invalid phone number format. Use 254XXXXXXXXX');
    }
    
    // Validate frequency
    $validFrequencies = ['1', '2', '3', '4', '5', '6', '7', '8'];
    if (!in_array($frequency, $validFrequencies)) {
        throw new InvalidArgumentException('Invalid frequency');
    }
    
    // Validate transaction type
    $validTransactionTypes = ['Standing Order Customer Pay Bill', 'Standing Order Customer Pay Marchant'];
    if (!in_array($transactionType, $validTransactionTypes)) {
        throw new InvalidArgumentException('Invalid transaction type');
    }
    
    // Validate receiver party identifier type
    $validReceiverTypes = ['2', '4'];
    if (!in_array($receiverPartyIdentifierType, $validReceiverTypes)) {
        throw new InvalidArgumentException('Invalid receiver party identifier type');
    }
    
    return true;
}
```

### 2. Implement Rate Limiting

```php
use Illuminate\Cache\RateLimiter;

public function createStandingOrderWithRateLimit($orderName, $startDate, $endDate, $businessShortCode, $amount, $phoneNumber, $callBackURL, $accountReference, $transactionDesc, $frequency, $transactionType, $receiverPartyIdentifierType)
{
    $rateLimiter = app(RateLimiter::class);
    $key = "standing_order:{$phoneNumber}";
    
    if ($rateLimiter->tooManyAttempts($key, 5)) { // 5 standing orders per hour per customer
        throw new Exception('Too many standing order attempts for this customer');
    }
    
    $rateLimiter->hit($key, 3600); // 1 hour window
    
    return $this->mpesa->createRatibaStandingOrder($orderName, $startDate, $endDate, $businessShortCode, $amount, $phoneNumber, $callBackURL, $accountReference, $transactionDesc, $frequency, $transactionType, $receiverPartyIdentifierType);
}
```

### 3. Standing Order Verification

```php
class StandingOrderVerificationService
{
    public function verifyStandingOrder($conversationId)
    {
        // Check standing order status in database
        $standingOrder = StandingOrder::where('conversation_id', $conversationId)->first();
        
        if (!$standingOrder) {
            throw new Exception('Standing order not found');
        }
        
        // Check if standing order is still active
        if ($standingOrder->status !== 'active') {
            throw new Exception('Standing order is not active');
        }
        
        // Check if end date has passed
        if (strtotime($standingOrder->end_date) < time()) {
            $standingOrder->update(['status' => 'expired']);
            throw new Exception('Standing order has expired');
        }
        
        return $standingOrder;
    }
}
```

## Troubleshooting

### Common Issues

1. **Invalid Date Format**
   - Ensure dates are in YYYYMMDD format
   - Check start date is before end date
   - Verify dates are not in the past

2. **Invalid Frequency**
   - Use only supported frequency codes (1-8)
   - Ensure frequency matches business requirements
   - Check frequency validation logic

3. **Invalid Phone Number**
   - Ensure phone number starts with 254
   - Verify phone number is registered with M-Pesa
   - Check phone number format validation

4. **Invalid Transaction Type**
   - Use correct transaction type strings
   - Match transaction type with receiver party identifier type
   - Verify business shortcode type

5. **Callback URL Issues**
   - Ensure callback URLs are accessible
   - Check URL format and SSL certificates
   - Verify callback handling logic

### Debug Mode

```php
// Enable debug logging
Log::channel('standing_order')->info('Standing order creation request', [
    'order_name' => $orderName,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'amount' => $amount,
    'phone_number' => $phoneNumber,
    'frequency' => $frequency,
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
    $response = $mpesa->createRatibaStandingOrder($orderName, $startDate, $endDate, $businessShortCode, $amount, $phoneNumber, $callBackURL, $accountReference, $transactionDesc, $frequency, $transactionType, $receiverPartyIdentifierType);
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    Log::error('Standing order validation failed: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other errors
    Log::error('Standing order creation failed: ' . $e->getMessage());
}
```

### Response Validation

```php
public function validateStandingOrderResponse($response)
{
    if (!isset($response['ResponseCode'])) {
        throw new Exception('Invalid response format');
    }
    
    if ($response['ResponseCode'] !== '0') {
        throw new Exception('Standing order creation failed: ' . ($response['ResponseDescription'] ?? 'Unknown error'));
    }
    
    return $response;
}
```

## Testing

### Unit Tests

```php
public function test_standing_order_creation()
{
    $mpesa = new Mpesa();
    
    $response = $mpesa->createRatibaStandingOrder(
        'Test Standing Order',
        '20240101',
        '20241231',
        '123456',
        '10000',
        '254700000000',
        'https://your-domain.com/mpesa/ratiba/callback',
        'TEST-001',
        'Test Payment',
        '4', // Monthly
        'Standing Order Customer Pay Bill',
        '4' // PayBill
    );
    
    $this->assertArrayHasKey('ResponseCode', $response);
    $this->assertArrayHasKey('ConversationID', $response);
    $this->assertEquals('0', $response['ResponseCode']);
}
```

### Integration Tests

```php
public function test_standing_order_workflow()
{
    $mpesa = new Mpesa();
    
    // Test standing order creation
    $response = $mpesa->createRatibaStandingOrder(
        'Integration Test Order',
        '20240101',
        '20241231',
        '123456',
        '50000',
        '254700000000',
        'https://your-domain.com/mpesa/ratiba/callback',
        'INTEGRATION-001',
        'Integration Test',
        '4', // Monthly
        'Standing Order Customer Pay Bill',
        '4' // PayBill
    );
    
    $this->assertEquals('0', $response['ResponseCode']);
    
    // Test callback handling
    if (isset($response['ConversationID'])) {
        // Wait for callback or check status manually
        $this->assertNotEmpty($response['ConversationID']);
    }
}
```

## Best Practices Summary

1. **Always validate input data** before creating standing orders
2. **Use appropriate frequency codes** for different payment cycles
3. **Implement proper error handling** and logging
4. **Set up callback URLs** to handle payment notifications
5. **Test thoroughly** in sandbox environment before production
6. **Monitor standing order success rates** and investigate failures
7. **Implement rate limiting** to prevent abuse
8. **Maintain audit trails** for all standing orders
9. **Keep security credentials secure** and rotate regularly
10. **Regularly reconcile** standing order payments with customer records
