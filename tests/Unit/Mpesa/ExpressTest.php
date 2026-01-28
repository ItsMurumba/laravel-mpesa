<?php

use Itsmurumba\Mpesa\Mpesa;

describe('Mpesa Express (STK Push)', function () {
    it('builds the correct payload for express payment', function () {
        $mpesa = new Mpesa();

        $calls = [];
        $fakeClient = makeFakeGuzzleClient($calls);

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
});

