<?php

use Itsmurumba\Mpesa\Mpesa;

function setProtected(object $object, string $property, mixed $value): void
{
    $ref = new ReflectionClass($object);
    while (! $ref->hasProperty($property) && $ref = $ref->getParentClass()) {
        // keep walking up
    }

    $prop = $ref->getProperty($property);
    $prop->setAccessible(true);
    $prop->setValue($object, $value);
}

it('throws for invalid dynamic qr transaction type', function () {
    $mpesa = new Mpesa();

    $mpesa->generateDynamicQRCode(
        merchantName: 'Shop',
        transactionReference: 'INV-1',
        amount: 10,
        transactionType: 'INVALID',
        creditPartyIdentifier: '123456'
    );
})->throws(InvalidArgumentException::class, 'Invalid transaction type');

it('throws for invalid ratiba frequency', function () {
    $mpesa = new Mpesa();

    $mpesa->createRatibaStandingOrder(
        orderName: 'Rent',
        startDate: '20260101',
        endDate: '20261231',
        businessShortCode: null,
        amount: '100',
        phoneNumber: '254700000000',
        callBackURL: null,
        accountReference: 'ACC1',
        transactionDesc: 'Rent',
        frequency: '9',
        transactionType: 'Standing Order Customer Pay Bill',
        receiverPartyIdentifierType: '4'
    );
})->throws(InvalidArgumentException::class, 'Invalid frequency');

it('throws for invalid ratiba receiver party identifier type', function () {
    $mpesa = new Mpesa();

    $mpesa->createRatibaStandingOrder(
        orderName: 'Rent',
        startDate: '20260101',
        endDate: '20261231',
        businessShortCode: null,
        amount: '100',
        phoneNumber: '254700000000',
        callBackURL: null,
        accountReference: 'ACC1',
        transactionDesc: 'Rent',
        frequency: '1',
        transactionType: 'Standing Order Customer Pay Bill',
        receiverPartyIdentifierType: '9'
    );
})->throws(InvalidArgumentException::class, 'Invalid receiver Party Identifier Type');

it('builds the correct payload for express payment', function () {
    $mpesa = new Mpesa();

    $calls = [];
    $fakeClient = new class($calls) {
        public array $calls = [];

        public function __construct(&$calls)
        {
            $this->calls = &$calls;
        }

        public function post(string $url, array $options)
        {
            $this->calls[] = ['method' => 'post', 'url' => $url, 'options' => $options];

            return new class {
                public function getBody()
                {
                    return new class {
                        public function getContents()
                        {
                            return json_encode(['ok' => true]);
                        }
                    };
                }
            };
        }
    };

    setProtected($mpesa, 'client', $fakeClient);
    setProtected($mpesa, 'baseUrl', 'https://example.test');
    setProtected($mpesa, 'lipaNaMpesaShortcode', '654321');
    setProtected($mpesa, 'lipaNaMpesaPasskey', 'passkey');
    setProtected($mpesa, 'lipaNaMpesaCallbackURL', 'https://example.test/stk-callback');

    $response = $mpesa->expressPayment(
        amount: 100,
        phoneNumber: '254700000000',
        accountReference: 'CompanyXLTD',
        transactionDescription: 'Payment of X'
    );

    expect($response)->toContain('"ok":true');
    expect($fakeClient->calls)->toHaveCount(1);

    $call = $fakeClient->calls[0];
    expect($call['url'])->toBe('https://example.test/mpesa/stkpush/v1/processrequest');

    $payload = json_decode($call['options']['body'], true);
    expect($payload['BusinessShortCode'])->toBe('654321');
    expect($payload['Amount'])->toBe(100);
    expect($payload['PhoneNumber'])->toBe('254700000000');
    expect($payload['CallBackURL'])->toBe('https://example.test/stk-callback');
    expect($payload['TransactionType'])->toBe('CustomerPayBillOnline');
    expect($payload)->toHaveKeys([
        'Password',
        'Timestamp',
        'PartyA',
        'PartyB',
        'AccountReference',
        'TransactionDesc',
    ]);
});

