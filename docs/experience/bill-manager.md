# M-Pesa Bill Manager API

## Overview

The M-Pesa Bill Manager API enables organizations to create, send, and manage electronic invoices through the M-Pesa platform. This comprehensive solution allows businesses to send customized e-invoices to customers, process payments, and handle reconciliation automatically.

## Key Features

- **Organization Opt-in**: Register your organization for Bill Manager services
- **Single Invoicing**: Send individual customized e-invoices to customers
- **Bulk Invoicing**: Send multiple invoices simultaneously
- **Payment Reconciliation**: Process payments and send e-receipts
- **Invoice Management**: Cancel single or bulk invoices
- **Profile Updates**: Update organization opt-in details
- **SMS Reminders**: Automatic payment reminders to customers
- **E-receipts**: Automated receipt generation and delivery

## Configuration

### Environment Setup

```php
// config/mpesa.php
return [
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'), // sandbox or production
    'baseUrl' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
    'lipaNaMpesaShortcode' => env('MPESA_LIPA_NA_MPESA_SHORTCODE', ''),
    'lipaNaMpesaCallbackURL' => env('MPESA_LIPA_NA_MPESA_CALLBACK_URL', 'https://your-domain.com/mpesa/bill-manager/callback'),
];
```

### Required Environment Variables

```env
MPESA_ENVIRONMENT=sandbox
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_LIPA_NA_MPESA_SHORTCODE=your_shortcode
MPESA_LIPA_NA_MPESA_CALLBACK_URL=https://your-domain.com/mpesa/bill-manager/callback
```

## Usage

### 1. Organization Opt-in

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

$response = $mpesa->billManagerOptInTo(
    shortcode: '123456',
    email: 'billing@yourcompany.com',
    officialContact: '254700000000',
    sendReminders: 1, // 1=Enable, 0=Disable
    logo: 'https://your-domain.com/logo.png',
    callbackURL: 'https://your-domain.com/mpesa/bill-manager/callback'
);
```

### 2. Single Invoicing

```php
$response = $mpesa->billManagerSingleInvoicing(
    externalReference: 'INV-2024-001',
    billedFullName: 'John Doe',
    billedPhoneNumber: '254700000000',
    billedPeriod: 'January 2024',
    invoiceName: 'Monthly Subscription',
    dueDate: '2024-02-15',
    accountReference: 'ACC001',
    amount: 1000,
    invoiceItems: [
        [
            'itemName' => 'Premium Plan',
            'quantity' => 1,
            'unitPrice' => 1000,
            'subTotal' => 1000
        ]
    ]
);
```

### 3. Bulk Invoicing

```php
$invoices = [
    [
        'externalReference' => 'INV-2024-001',
        'billedFullName' => 'John Doe',
        'billedPhoneNumber' => '254700000000',
        'billedPeriod' => 'January 2024',
        'invoiceName' => 'Monthly Subscription',
        'dueDate' => '2024-02-15',
        'accountReference' => 'ACC001',
        'amount' => 1000,
        'invoiceItems' => [
            [
                'itemName' => 'Premium Plan',
                'quantity' => 1,
                'unitPrice' => 1000,
                'subTotal' => 1000
            ]
        ]
    ],
    [
        'externalReference' => 'INV-2024-002',
        'billedFullName' => 'Jane Smith',
        'billedPhoneNumber' => '254711111111',
        'billedPeriod' => 'January 2024',
        'invoiceName' => 'Monthly Subscription',
        'dueDate' => '2024-02-15',
        'accountReference' => 'ACC002',
        'amount' => 1500,
        'invoiceItems' => [
            [
                'itemName' => 'Enterprise Plan',
                'quantity' => 1,
                'unitPrice' => 1500,
                'subTotal' => 1500
            ]
        ]
    ]
];

$response = $mpesa->billManagerBulkInvoicing($invoices);
```

### 4. Payment Reconciliation

```php
$response = $mpesa->billManagerPaymentReconciliation(
    transactionId: 'QK123456789',
    paidAmount: 1000,
    customerPhoneNumber: '254700000000',
    dateCreated: '2024-01-15T10:30:00',
    accountReference: 'ACC001',
    shortCode: '123456'
);
```

### 5. Cancel Single Invoice

```php
$response = $mpesa->billManagerCancelSingleInvoice('INV-2024-001');
```

### 6. Cancel Bulk Invoices

```php
$externalReferences = ['INV-2024-001', 'INV-2024-002'];
$response = $mpesa->billManagerCancelBulkInvoices($externalReferences);
```

### 7. Update Opt-in Details

```php
$response = $mpesa->billManagerUpdateOptinDetails(
    shortcode: '123456',
    email: 'new-billing@yourcompany.com',
    officialContact: '254700000000',
    sendReminders: 1,
    logo: 'https://your-domain.com/new-logo.png',
    callbackurl: 'https://your-domain.com/mpesa/bill-manager/new-callback'
);
```

## Request Parameters

### Opt-in Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `shortcode` | string | Yes | Organization's shortcode (Paybill or Buygoods) |
| `email` | string | Yes | Official contact email address |
| `officialContact` | string | Yes | Official contact phone number |
| `sendReminders` | int | Yes | Flag to enable/disable SMS reminders (1=Enable, 0=Disable) |
| `logo` | string | Yes | Image URL to embed in invoices and receipts |
| `callbackURL` | string | No | Callback URL for payment notifications |

### Single Invoicing Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `externalReference` | string | Yes | Unique invoice reference |
| `billedFullName` | string | Yes | Customer's full name |
| `billedPhoneNumber` | string | Yes | Customer's phone number |
| `billedPeriod` | string | Yes | Billing period (e.g., "January 2024") |
| `invoiceName` | string | Yes | Descriptive invoice name |
| `dueDate` | string | Yes | Payment due date (YYYY-MM-DD) |
| `accountReference` | string | Yes | Customer account reference |
| `amount` | numeric | Yes | Total invoice amount in KES |
| `invoiceItems` | array | No | Additional billable items |

### Payment Reconciliation Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `transactionId` | string | Yes | M-Pesa transaction reference |
| `paidAmount` | numeric | Yes | Amount paid in KES |
| `customerPhoneNumber` | string | Yes | Customer's phone number |
| `dateCreated` | string | Yes | Payment date and time |
| `accountReference` | string | Yes | Customer account reference |
| `shortCode` | numeric | Yes | Organization shortcode |

## Response Format

### Success Response (Opt-in)

```json
{
    "rescode": "000",
    "resmsg": "Success",
    "status": "success"
}
```

### Success Response (Invoicing)

```json
{
    "rescode": "000",
    "resmsg": "Success",
    "status": "success",
    "data": {
        "externalReference": "INV-2024-001",
        "invoiceId": "12345"
    }
}
```

### Error Response

```json
{
    "rescode": "001",
    "resmsg": "Invalid shortcode",
    "status": "error"
}
```

## Callback Handling

### Payment Notification Callback

```php
// routes/web.php
Route::post('/mpesa/bill-manager/callback', function (Request $request) {
    $data = $request->all();
    
    // Verify the callback
    if (isset($data['transactionId'])) {
        // Process payment notification
        $transactionId = $data['transactionId'];
        $paidAmount = $data['paidAmount'];
        $customerPhone = $data['msisdn'];
        $accountReference = $data['accountReference'];
        
        // Update invoice status
        Invoice::where('external_reference', $accountReference)
            ->update(['status' => 'paid', 'paid_amount' => $paidAmount]);
        
        // Send confirmation to customer
        $this->sendPaymentConfirmation($customerPhone, $paidAmount);
    }
    
    return response()->json(['status' => 'received']);
});
```

## Real-World Usage Examples

### Subscription Billing System

```php
class SubscriptionBillingService
{
    public function generateMonthlyInvoices()
    {
        $mpesa = new Mpesa();
        $subscriptions = Subscription::where('status', 'active')->get();
        
        $invoices = [];
        
        foreach ($subscriptions as $subscription) {
            $invoices[] = [
                'externalReference' => "INV-{$subscription->id}-" . date('Y-m'),
                'billedFullName' => $subscription->customer->name,
                'billedPhoneNumber' => $subscription->customer->phone,
                'billedPeriod' => date('F Y'),
                'invoiceName' => $subscription->plan->name . ' Subscription',
                'dueDate' => date('Y-m-d', strtotime('+15 days')),
                'accountReference' => $subscription->id,
                'amount' => $subscription->plan->price,
                'invoiceItems' => [
                    [
                        'itemName' => $subscription->plan->name,
                        'quantity' => 1,
                        'unitPrice' => $subscription->plan->price,
                        'subTotal' => $subscription->plan->price
                    ]
                ]
            ];
        }
        
        if (!empty($invoices)) {
            $response = $mpesa->billManagerBulkInvoicing($invoices);
            
            if (isset($response['rescode']) && $response['rescode'] === '000') {
                // Log successful invoice generation
                Log::info('Bulk invoices generated successfully', ['count' => count($invoices)]);
            }
        }
    }
}
```

### Utility Company Billing

```php
class UtilityBillingService
{
    public function sendUtilityBill($customer, $usage, $amount)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->billManagerSingleInvoicing(
            externalReference: "UTIL-{$customer->id}-" . date('Y-m'),
            billedFullName: $customer->name,
            billedPhoneNumber: $customer->phone,
            billedPeriod: date('F Y'),
            invoiceName: 'Electricity Bill',
            dueDate: date('Y-m-d', strtotime('+30 days')),
            accountReference: $customer->account_number,
            amount: $amount,
            invoiceItems: [
                [
                    'itemName' => 'Electricity Usage',
                    'quantity' => $usage,
                    'unitPrice' => 15.50,
                    'subTotal' => $amount
                ]
            ]
        );
        
        if (isset($response['rescode']) && $response['rescode'] === '000') {
            // Update customer billing record
            $customer->bills()->create([
                'external_reference' => "UTIL-{$customer->id}-" . date('Y-m'),
                'amount' => $amount,
                'due_date' => date('Y-m-d', strtotime('+30 days')),
                'status' => 'sent'
            ]);
        }
        
        return $response;
    }
}
```

### Payment Processing Service

```php
class PaymentProcessingService
{
    public function processPayment($transactionId, $paidAmount, $customerPhone, $accountReference)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->billManagerPaymentReconciliation(
            transactionId: $transactionId,
            paidAmount: $paidAmount,
            customerPhoneNumber: $customerPhone,
            dateCreated: now()->toISOString(),
            accountReference: $accountReference,
            shortCode: config('mpesa.lipaNaMpesaShortcode')
        );
        
        if (isset($response['rescode']) && $response['rescode'] === '000') {
            // Update invoice status
            $invoice = Invoice::where('external_reference', $accountReference)->first();
            if ($invoice) {
                $invoice->update([
                    'status' => 'paid',
                    'paid_amount' => $paidAmount,
                    'paid_at' => now(),
                    'transaction_id' => $transactionId
                ]);
                
                // Send e-receipt
                $this->sendEReceipt($invoice, $paidAmount);
            }
        }
        
        return $response;
    }
    
    private function sendEReceipt($invoice, $paidAmount)
    {
        // Send SMS or email receipt to customer
        $message = "Payment received: KES {$paidAmount} for invoice {$invoice->external_reference}. Thank you!";
        
        // Implement SMS/email sending logic
        SMS::send($invoice->customer->phone, $message);
    }
}
```

## Security Best Practices

### 1. Input Validation

```php
public function validateInvoiceData($data)
{
    $rules = [
        'externalReference' => 'required|string|max:50|unique:invoices',
        'billedFullName' => 'required|string|max:100',
        'billedPhoneNumber' => 'required|regex:/^254[0-9]{9}$/',
        'billedPeriod' => 'required|string|max:20',
        'invoiceName' => 'required|string|max:100',
        'dueDate' => 'required|date|after:today',
        'accountReference' => 'required|string|max:50',
        'amount' => 'required|numeric|min:1|max:70000'
    ];
    
    $validator = Validator::make($data, $rules);
    
    if ($validator->fails()) {
        throw new InvalidArgumentException($validator->errors()->first());
    }
    
    return true;
}
```

### 2. Rate Limiting

```php
use Illuminate\Cache\RateLimiter;

public function sendInvoiceWithRateLimit($invoiceData)
{
    $rateLimiter = app(RateLimiter::class);
    $key = "billmanager:{$invoiceData['billedPhoneNumber']}";
    
    if ($rateLimiter->tooManyAttempts($key, 10)) { // 10 invoices per day
        throw new Exception('Too many invoice attempts for this customer');
    }
    
    $rateLimiter->hit($key, 86400); // 24 hours
    
    return $this->mpesa->billManagerSingleInvoicing(...$invoiceData);
}
```

### 3. Audit Trail

```php
class BillManagerAuditService
{
    public function logInvoiceRequest($data, $response)
    {
        BillManagerAudit::create([
            'action' => 'invoice_sent',
            'external_reference' => $data['externalReference'],
            'customer_phone' => $data['billedPhoneNumber'],
            'amount' => $data['amount'],
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

1. **Invalid Shortcode**
   - Ensure the shortcode is registered and active
   - Verify the shortcode format (5-6 digits)

2. **Invalid Phone Number Format**
   - Phone numbers must be in format: 254XXXXXXXXX
   - Remove any spaces or special characters

3. **Duplicate External Reference**
   - Each invoice must have a unique external reference
   - Use timestamp or UUID to ensure uniqueness

4. **Invalid Due Date**
   - Due date must be in the future
   - Use YYYY-MM-DD format

5. **Amount Validation**
   - Amount must be between 1 and 70,000 KES
   - Ensure amount matches invoice items total

### Debug Mode

```php
// Enable debug logging
Log::channel('billmanager')->info('Invoice request', [
    'external_reference' => $externalReference,
    'customer_phone' => $billedPhoneNumber,
    'amount' => $amount,
    'timestamp' => now()
]);
```

## Package Features

### Automatic Configuration

The package automatically handles:
- Environment-specific base URLs
- Default shortcode and callback URL configuration
- Request/response formatting
- Error handling

### Error Handling

```php
try {
    $response = $mpesa->billManagerSingleInvoicing($invoiceData);
} catch (IsNullException $e) {
    // Handle missing configuration
    Log::error('Bill Manager configuration missing: ' . $e->getMessage());
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    Log::error('Bill Manager validation error: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other errors
    Log::error('Bill Manager error: ' . $e->getMessage());
}
```

### Response Validation

```php
public function validateBillManagerResponse($response)
{
    if (!isset($response['rescode'])) {
        throw new Exception('Invalid response format');
    }
    
    if ($response['rescode'] !== '000') {
        throw new Exception('Bill Manager operation failed: ' . ($response['resmsg'] ?? 'Unknown error'));
    }
    
    return $response;
}
```

## Testing

### Unit Tests

```php
public function test_bill_manager_opt_in()
{
    $mpesa = new Mpesa();
    
    $response = $mpesa->billManagerOptInTo(
        '123456',
        'test@example.com',
        '254700000000',
        1,
        'https://example.com/logo.png'
    );
    
    $this->assertArrayHasKey('rescode', $response);
    $this->assertArrayHasKey('status', $response);
}
```

### Integration Tests

```php
public function test_single_invoicing_workflow()
{
    $mpesa = new Mpesa();
    
    // First opt-in
    $optInResponse = $mpesa->billManagerOptInTo(
        '123456',
        'test@example.com',
        '254700000000',
        1,
        'https://example.com/logo.png'
    );
    
    $this->assertEquals('000', $optInResponse['rescode']);
    
    // Then send invoice
    $invoiceResponse = $mpesa->billManagerSingleInvoicing(
        'TEST-INV-001',
        'Test Customer',
        '254700000000',
        'January 2024',
        'Test Invoice',
        '2024-02-15',
        'ACC001',
        1000
    );
    
    $this->assertEquals('000', $invoiceResponse['rescode']);
}
```

## Best Practices Summary

1. **Always validate input data** before sending invoices
2. **Use unique external references** for each invoice
3. **Implement proper error handling** and logging
4. **Set up callback URLs** to handle payment notifications
5. **Test thoroughly** in sandbox environment before production
6. **Monitor invoice delivery rates** and investigate failures
7. **Implement rate limiting** to prevent abuse
8. **Maintain audit trails** for all operations
9. **Keep customer data secure** and comply with privacy regulations
10. **Regularly update opt-in details** to keep information current
