<?php

use Itsmurumba\Mpesa\Mpesa;

describe('Ratiba (Standing Orders)', function () {
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

    it('defaults business shortcode and callback url when null', function () {
        $mpesa = new Mpesa();

        $calls = [];
        $fakeClient = makeFakeGuzzleClient($calls);

        setProtected($mpesa, 'client', $fakeClient);
        setProtected($mpesa, 'baseUrl', 'https://example.test');
        setProtected($mpesa, 'lipaNaMpesaShortcode', '654321');
        setProtected($mpesa, 'callBackURL', 'https://example.test/callback');

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
            receiverPartyIdentifierType: '4'
        );

        expect($fakeClient->calls)->toHaveCount(1);
        expect($fakeClient->calls[0]['url'])->toBe('https://example.test/standingorder/v1/createStandingOrderExternal');

        $payload = json_decode($fakeClient->calls[0]['options']['body'], true);
        expect($payload['BusinessShortCode'])->toBe('654321');
        expect($payload['CallBackURL'])->toBe('https://example.test/callback');
    });
});

