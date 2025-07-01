# M-Pesa Dynamic QR Code API

## Overview

The M-Pesa Dynamic QR Code API enables businesses to generate dynamic QR codes for various types of M-Pesa transactions. These QR codes can be scanned by customers using the M-Pesa app to initiate payments, making it easy to accept mobile payments in physical locations or through digital channels.

## Key Features

- **Dynamic QR Generation**: Create unique QR codes for each transaction
- **Multiple Transaction Types**: Support for Buy Goods, Pay Bill, Send Money, and more
- **Customizable Size**: Adjustable QR code dimensions
- **Real-time Payment**: Instant payment processing through M-Pesa
- **Merchant Branding**: Include merchant name in QR codes
- **Transaction Tracking**: Unique reference numbers for each transaction
- **Secure Transactions**: Encrypted and secure payment processing

## Configuration

### Environment Setup

```php
// config/mpesa.php
return [
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'), // sandbox or production
    'baseUrl' => env('MPESA_BASE_URL', 'https://sandbox.safaricom.co.ke'),
    'consumerKey' => env('MPESA_CONSUMER_KEY', ''),
    'consumerSecret' => env('MPESA_CONSUMER_SECRET', ''),
];
```

### Required Environment Variables

```env
MPESA_ENVIRONMENT=sandbox
MPESA_BASE_URL=https://sandbox.safaricom.co.ke
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
```

## Usage

### Basic QR Code Generation

```php
use Itsmurumba\Mpesa\Mpesa;

$mpesa = new Mpesa();

$response = $mpesa->generateDynamicQRCode(
    merchantName: 'My Store',
    transactionReference: 'TXN-2024-001',
    amount: 1000,
    transactionType: 'BG', // Buy Goods
    creditPartyIdentifier: '123456', // Till number
    size: '300'
);
```

### Advanced Usage with Error Handling

```php
use Itsmurumba\Mpesa\Mpesa;
use InvalidArgumentException;
use Exception;

try {
    $mpesa = new Mpesa();
    
    $response = $mpesa->generateDynamicQRCode(
        merchantName: 'Coffee Shop',
        transactionReference: 'COFFEE-' . time(),
        amount: 250,
        transactionType: 'BG',
        creditPartyIdentifier: '123456',
        size: '400'
    );
    
    // Handle successful QR generation
    if (isset($response['QRCode'])) {
        $qrCodeData = $response['QRCode'];
        $requestId = $response['RequestID'];
        
        // Store QR code data for tracking
        QRCodeTransaction::create([
            'transaction_reference' => 'COFFEE-' . time(),
            'amount' => 250,
            'qr_code_data' => $qrCodeData,
            'request_id' => $requestId,
            'status' => 'generated'
        ]);
        
        // Display QR code to customer
        $this->displayQRCode($qrCodeData);
    }
    
} catch (InvalidArgumentException $e) {
    // Handle invalid transaction type
    Log::error('Invalid transaction type: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other errors
    Log::error('QR code generation failed: ' . $e->getMessage());
}
```

### Different Transaction Types

```php
// Buy Goods (Till Number)
$buyGoodsQR = $mpesa->generateDynamicQRCode(
    'Retail Store',
    'BG-' . time(),
    500,
    'BG',
    '123456', // Till number
    '300'
);

// Pay Bill
$payBillQR = $mpesa->generateDynamicQRCode(
    'Utility Company',
    'UTIL-' . time(),
    1500,
    'PB',
    '123456', // Paybill number
    '300'
);

// Send Money
$sendMoneyQR = $mpesa->generateDynamicQRCode(
    'Money Transfer',
    'TRANSFER-' . time(),
    2000,
    'SM',
    '254700000000', // Phone number
    '300'
);

// Withdraw Agent
$withdrawQR = $mpesa->generateDynamicQRCode(
    'ATM Withdrawal',
    'ATM-' . time(),
    5000,
    'WA',
    '123456', // Agent number
    '300'
);

// Send Business
$businessQR = $mpesa->generateDynamicQRCode(
    'Business Payment',
    'BIZ-' . time(),
    3000,
    'SB',
    '123456', // Business shortcode
    '300'
);
```

## Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `merchantName` | string | Yes | The name of the merchant/business |
| `transactionReference` | string | Yes | A unique reference for the transaction |
| `amount` | float | Yes | The amount to be paid |
| `transactionType` | string | Yes | Type of transaction (BG, WA, PB, SM, SB) |
| `creditPartyIdentifier` | string | Yes | Till number, paybill, or phone number receiving payment |
| `size` | string | No | The size of the QR code in pixels (default: 300) |

### Transaction Types

| Code | Description | Use Case |
|------|-------------|----------|
| `BG` | Buy Goods | Retail purchases using till number |
| `WA` | Withdraw Agent | ATM withdrawals and agent transactions |
| `PB` | Pay Bill | Utility payments and bill payments |
| `SM` | Send Money | Person-to-person money transfers |
| `SB` | Send Business | Business-to-business payments |

## Response Format

### Success Response

```json
{
    "QRCode": "00020101021226550014COM.SAFARICOM011212345678901520459995303540540510005802KE530354054031005802KE6304",
    "RequestID": "12345678-1234-1234-1234-123456789012",
    "ResponseCode": "0",
    "ResponseDescription": "Success"
}
```

### Error Response

```json
{
    "RequestID": "12345678-1234-1234-1234-123456789012",
    "ResponseCode": "1",
    "ResponseDescription": "Invalid transaction type"
}
```

## Real-World Usage Examples

### Retail Store QR Code System

```php
class RetailQRCodeService
{
    public function generatePaymentQR($order)
    {
        $mpesa = new Mpesa();
        
        try {
            $response = $mpesa->generateDynamicQRCode(
                merchantName: $order->store->name,
                transactionReference: "ORDER-{$order->id}",
                amount: $order->total_amount,
                transactionType: 'BG',
                creditPartyIdentifier: $order->store->till_number,
                size: '400'
            );
            
            if (isset($response['QRCode'])) {
                // Store QR code data
                $order->update([
                    'qr_code_data' => $response['QRCode'],
                    'qr_request_id' => $response['RequestID'],
                    'qr_generated_at' => now()
                ]);
                
                // Generate QR code image
                $qrImage = $this->generateQRImage($response['QRCode']);
                
                return [
                    'qr_code' => $response['QRCode'],
                    'qr_image' => $qrImage,
                    'amount' => $order->total_amount,
                    'reference' => "ORDER-{$order->id}"
                ];
            }
            
        } catch (Exception $e) {
            Log::error("QR generation failed for order {$order->id}: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function generateQRImage($qrData)
    {
        // Use a QR code library to generate image
        $qrCode = QrCode::create($qrData);
        $qrCode->setSize(400);
        $qrCode->setMargin(10);
        
        return $qrCode->writeDataUri();
    }
}
```

### Event Ticket Payment System

```php
class EventTicketQRService
{
    public function generateTicketQR($ticket)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->generateDynamicQRCode(
            merchantName: $ticket->event->organizer_name,
            transactionReference: "TICKET-{$ticket->id}",
            amount: $ticket->price,
            transactionType: 'PB',
            creditPartyIdentifier: $ticket->event->paybill_number,
            size: '350'
        );
        
        if (isset($response['QRCode'])) {
            // Store QR code for ticket
            $ticket->update([
                'qr_code' => $response['QRCode'],
                'qr_generated_at' => now()
            ]);
            
            // Send QR code to customer via email/SMS
            $this->sendTicketQR($ticket, $response['QRCode']);
        }
        
        return $response;
    }
    
    private function sendTicketQR($ticket, $qrCode)
    {
        $message = "Your ticket QR code for {$ticket->event->name} is ready. Scan to pay KES {$ticket->price}";
        
        // Send SMS with QR code link
        SMS::send($ticket->customer->phone, $message);
        
        // Send email with QR code image
        Mail::to($ticket->customer->email)->send(new TicketQRMail($ticket, $qrCode));
    }
}
```

### Utility Payment QR System

```php
class UtilityQRService
{
    public function generateUtilityQR($customer, $bill)
    {
        $mpesa = new Mpesa();
        
        $response = $mpesa->generateDynamicQRCode(
            merchantName: 'Power Company Ltd',
            transactionReference: "BILL-{$bill->id}",
            amount: $bill->amount,
            transactionType: 'PB',
            creditPartyIdentifier: '123456', // Company paybill
            size: '300'
        );
        
        if (isset($response['QRCode'])) {
            // Store QR code with bill
            $bill->update([
                'qr_code' => $response['QRCode'],
                'qr_generated_at' => now()
            ]);
            
            // Send bill with QR code
            $this->sendBillWithQR($customer, $bill, $response['QRCode']);
        }
        
        return $response;
    }
    
    private function sendBillWithQR($customer, $bill, $qrCode)
    {
        // Generate bill PDF with QR code
        $pdf = PDF::loadView('bills.utility', [
            'customer' => $customer,
            'bill' => $bill,
            'qrCode' => $qrCode
        ]);
        
        // Send bill via email
        Mail::to($customer->email)->send(new UtilityBillMail($customer, $bill, $pdf));
        
        // Send SMS notification
        $message = "Your utility bill for KES {$bill->amount} is ready. Scan the QR code in your email to pay.";
        SMS::send($customer->phone, $message);
    }
}
```

## Security Best Practices

### 1. Validate Input Data

```php
public function validateQRRequest($merchantName, $amount, $transactionType, $creditPartyIdentifier)
{
    // Validate merchant name
    if (empty($merchantName) || strlen($merchantName) > 50) {
        throw new InvalidArgumentException('Invalid merchant name');
    }
    
    // Validate amount
    if ($amount <= 0 || $amount > 70000) {
        throw new InvalidArgumentException('Invalid amount');
    }
    
    // Validate transaction type
    $validTypes = ['BG', 'WA', 'PB', 'SM', 'SB'];
    if (!in_array($transactionType, $validTypes)) {
        throw new InvalidArgumentException('Invalid transaction type');
    }
    
    // Validate credit party identifier based on transaction type
    switch ($transactionType) {
        case 'BG':
        case 'WA':
        case 'PB':
        case 'SB':
            if (!preg_match('/^[0-9]{5,6}$/', $creditPartyIdentifier)) {
                throw new InvalidArgumentException('Invalid shortcode format');
            }
            break;
        case 'SM':
            if (!preg_match('/^254[0-9]{9}$/', $creditPartyIdentifier)) {
                throw new InvalidArgumentException('Invalid phone number format');
            }
            break;
    }
    
    return true;
}
```

### 2. Implement Rate Limiting

```php
use Illuminate\Cache\RateLimiter;

public function generateQRWithRateLimit($merchantName, $amount, $transactionType, $creditPartyIdentifier)
{
    $rateLimiter = app(RateLimiter::class);
    $key = "qr_generation:{$merchantName}";
    
    if ($rateLimiter->tooManyAttempts($key, 100)) { // 100 QR codes per hour
        throw new Exception('Too many QR code generation attempts');
    }
    
    $rateLimiter->hit($key, 3600); // 1 hour window
    
    return $this->mpesa->generateDynamicQRCode(
        $merchantName,
        'QR-' . time(),
        $amount,
        $transactionType,
        $creditPartyIdentifier
    );
}
```

### 3. QR Code Security

```php
class QRSecurityService
{
    public function generateSecureQR($transactionData)
    {
        // Add timestamp to prevent replay attacks
        $transactionData['timestamp'] = time();
        
        // Add nonce for uniqueness
        $transactionData['nonce'] = Str::random(16);
        
        // Generate QR code
        $response = $this->mpesa->generateDynamicQRCode(
            $transactionData['merchantName'],
            $transactionData['reference'],
            $transactionData['amount'],
            $transactionData['type'],
            $transactionData['identifier']
        );
        
        // Store QR code with security metadata
        $this->storeQRSecurityData($response['RequestID'], $transactionData);
        
        return $response;
    }
    
    private function storeQRSecurityData($requestId, $data)
    {
        QRSecurityLog::create([
            'request_id' => $requestId,
            'merchant_name' => $data['merchantName'],
            'amount' => $data['amount'],
            'timestamp' => $data['timestamp'],
            'nonce' => $data['nonce'],
            'ip_address' => request()->ip(),
            'created_at' => now()
        ]);
    }
}
```

## Troubleshooting

### Common Issues

1. **Invalid Transaction Type**
   - Ensure transaction type is one of: BG, WA, PB, SM, SB
   - Check case sensitivity (uppercase only)

2. **Invalid Credit Party Identifier**
   - For BG/WA/PB/SB: Use 5-6 digit shortcode
   - For SM: Use phone number in format 254XXXXXXXXX

3. **QR Code Not Scanning**
   - Ensure QR code size is appropriate (300-500 pixels recommended)
   - Check that QR code data is properly encoded
   - Verify merchant name is not too long

4. **Amount Validation**
   - Amount must be greater than 0 and less than 70,000 KES
   - Use numeric values only

5. **Duplicate Transaction Reference**
   - Each QR code should have a unique transaction reference
   - Use timestamps or UUIDs to ensure uniqueness

### Debug Mode

```php
// Enable debug logging
Log::channel('qr_code')->info('QR code generation request', [
    'merchant_name' => $merchantName,
    'amount' => $amount,
    'transaction_type' => $transactionType,
    'credit_party_identifier' => $creditPartyIdentifier,
    'timestamp' => now()
]);
```

## Package Features

### Automatic Configuration

The package automatically handles:
- Environment-specific base URLs
- Authentication and access token management
- Request/response formatting
- Error handling

### Error Handling

```php
try {
    $response = $mpesa->generateDynamicQRCode($merchantName, $reference, $amount, $type, $identifier);
} catch (InvalidArgumentException $e) {
    // Handle validation errors
    Log::error('QR code validation error: ' . $e->getMessage());
} catch (IsNullException $e) {
    // Handle missing configuration
    Log::error('M-Pesa configuration missing: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other errors
    Log::error('QR code generation error: ' . $e->getMessage());
}
```

### Response Validation

```php
public function validateQRResponse($response)
{
    if (!isset($response['ResponseCode'])) {
        throw new Exception('Invalid response format');
    }
    
    if ($response['ResponseCode'] !== '0') {
        throw new Exception('QR generation failed: ' . ($response['ResponseDescription'] ?? 'Unknown error'));
    }
    
    if (!isset($response['QRCode'])) {
        throw new Exception('QR code data not found in response');
    }
    
    return $response;
}
```

## Testing

### Unit Tests

```php
public function test_qr_code_generation()
{
    $mpesa = new Mpesa();
    
    $response = $mpesa->generateDynamicQRCode(
        'Test Merchant',
        'TEST-' . time(),
        100,
        'BG',
        '123456',
        '300'
    );
    
    $this->assertArrayHasKey('QRCode', $response);
    $this->assertArrayHasKey('RequestID', $response);
    $this->assertEquals('0', $response['ResponseCode']);
}
```

### Integration Tests

```php
public function test_different_transaction_types()
{
    $mpesa = new Mpesa();
    
    $transactionTypes = ['BG', 'PB', 'SM', 'WA', 'SB'];
    
    foreach ($transactionTypes as $type) {
        $response = $mpesa->generateDynamicQRCode(
            'Test Merchant',
            "TEST-{$type}-" . time(),
            100,
            $type,
            $type === 'SM' ? '254700000000' : '123456',
            '300'
        );
        
        $this->assertEquals('0', $response['ResponseCode']);
        $this->assertNotEmpty($response['QRCode']);
    }
}
```

## Best Practices Summary

1. **Always validate input data** before generating QR codes
2. **Use unique transaction references** for each QR code
3. **Implement proper error handling** and logging
4. **Choose appropriate QR code sizes** for your use case
5. **Test QR codes thoroughly** in sandbox environment
6. **Monitor QR code usage** and scan success rates
7. **Implement rate limiting** to prevent abuse
8. **Keep transaction references secure** and trackable
9. **Use appropriate transaction types** for your business needs
10. **Store QR code data** for reconciliation purposes
