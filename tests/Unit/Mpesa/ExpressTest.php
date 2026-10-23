<?php

use Itsmurumba\Mpesa\Exceptions\MpesaException;
use Itsmurumba\Mpesa\Mpesa;
use Itsmurumba\Mpesa\MpesaManager;

describe('Mpesa Express (STK Push)', function () {
    it('builds the correct payload for a paybill by default', function () {
        $response = (new Mpesa())->expressPayment(
            amount: 100,
            phoneNumber: '254700000000',
            accountReference: 'CompanyXLTD',
            transactionDescription: 'Payment of X'
        );

        expect($response)->toContain('"ok":true');
        expect(darajaRequests())->toHaveCount(1);

        $request = darajaRequests()[0];
        expect($request->url())->toBe('https://example.test/mpesa/stkpush/v1/processrequest');
        expect($request->method())->toBe('POST');

        $payload = $request->data();
        expect($payload['BusinessShortCode'])->toBe('654321');
        expect($payload['PartyB'])->toBe('654321');
        expect($payload['Amount'])->toBe(100);
        expect($payload['PhoneNumber'])->toBe('254700000000');
        expect($payload['CallBackURL'])->toBe('https://example.test/stk-callback');
        expect($payload['TransactionType'])->toBe('CustomerPayBillOnline');
        expect($payload)->toHaveKeys([
            'Password',
            'Timestamp',
            'PartyA',
            'AccountReference',
            'TransactionDesc',
        ]);
    });

    it('pays a till that is its own shortcode, with PartyB the same as BusinessShortCode', function () {
        (new MpesaManager())->for('tenant-y', [
            'lipaNaMpesaShortcode' => '5566778',
            'lipaNaMpesaPasskey' => 'till-passkey',
            'lipaNaMpesaTransactionType' => 'CustomerBuyGoodsOnline',
        ])->expressPayment(50, '254711111111');

        $payload = darajaRequests()[0]->data();
        expect($payload['TransactionType'])->toBe('CustomerBuyGoodsOnline');
        expect($payload['BusinessShortCode'])->toBe('5566778');
        expect($payload['PartyB'])->toBe('5566778');
    });

    it('rejects a transaction type Daraja does not accept, before sending anything', function () {
        expect(fn () => (new MpesaManager())->for('tenant-z', [
            'lipaNaMpesaTransactionType' => 'CustomerBuyGoods',
        ])->expressPayment(50, '254711111111'))->toThrow(MpesaException::class);

        expect(darajaRequests())->toHaveCount(0);
    });

    it('pays a till whose passkey was issued to another shortcode, with the till as PartyB', function () {
        (new MpesaManager())->for('tenant-x', [
            'lipaNaMpesaShortcode' => '174379',
            'lipaNaMpesaPasskey' => 'head-office-passkey',
            'lipaNaMpesaTransactionType' => 'CustomerBuyGoodsOnline',
            'lipaNaMpesaTillNumber' => '5566778',
        ])->expressPayment(50, '254711111111');

        $payload = darajaRequests()[0]->data();
        expect($payload['TransactionType'])->toBe('CustomerBuyGoodsOnline');
        expect($payload['BusinessShortCode'])->toBe('174379');
        expect($payload['PartyB'])->toBe('5566778');
        expect($payload['Password'])->toBe(base64_encode('174379' . 'head-office-passkey' . $payload['Timestamp']));
    });
});
