<?php

use Itsmurumba\Mpesa\Mpesa;

describe('Dynamic QR', function () {
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

    it('sends the expected payload for dynamic qr generation', function () {
        $mpesa = new Mpesa();

        $calls = [];
        $fakeClient = makeFakeGuzzleClient($calls);

        setProtected($mpesa, 'client', $fakeClient);
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $mpesa->generateDynamicQRCode('Shop', 'INV-1', 10, 'PB', '123456', '400');

        expect($fakeClient->calls)->toHaveCount(1);
        expect($fakeClient->calls[0]['url'])->toBe('https://example.test/mpesa/qrcode/v1/generate');

        $payload = json_decode($fakeClient->calls[0]['options']['body'], true);
        expect($payload)->toMatchArray([
            'MerchantName' => 'Shop',
            'RefNo' => 'INV-1',
            'Amount' => 10,
            'TrxCode' => 'PB',
            'CPI' => '123456',
            'Size' => '400',
        ]);
    });
});

