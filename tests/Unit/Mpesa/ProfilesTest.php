<?php

use Itsmurumba\Mpesa\MpesaManager;

describe('Profiles (multi-tenant)', function () {
    it('resolves different profiles via MpesaManager', function () {
        config()->set('mpesa', array_merge(config('mpesa'), [
            'default_profile' => 'default',
            'profiles' => [
                'default' => array_merge(config('mpesa'), [
                    'consumerKey' => 'key-a',
                    'consumerSecret' => 'secret-a',
                    'lipaNaMpesaShortcode' => '111111',
                    'lipaNaMpesaPasskey' => 'passkey-a',
                ]),
                'tenant_b' => array_merge(config('mpesa'), [
                    'consumerKey' => 'key-b',
                    'consumerSecret' => 'secret-b',
                    'lipaNaMpesaShortcode' => '222222',
                    'lipaNaMpesaPasskey' => 'passkey-b',
                ]),
            ],
        ]));

        $manager = new MpesaManager();

        $callsA = [];
        $fakeClientA = makeFakeGuzzleClient($callsA);
        $mpesaA = $manager->for('default');
        setProtected($mpesaA, 'client', $fakeClientA);
        setProtected($mpesaA, 'baseUrl', 'https://example.test');

        $callsB = [];
        $fakeClientB = makeFakeGuzzleClient($callsB);
        $mpesaB = $manager->for('tenant_b');
        setProtected($mpesaB, 'client', $fakeClientB);
        setProtected($mpesaB, 'baseUrl', 'https://example.test');

        $mpesaA->expressPayment(10, '254700000000');
        $mpesaB->expressPayment(10, '254700000000');

        $payloadA = json_decode($fakeClientA->calls[0]['options']['body'], true);
        $payloadB = json_decode($fakeClientB->calls[0]['options']['body'], true);

        expect($payloadA['BusinessShortCode'])->toBe('111111');
        expect($payloadB['BusinessShortCode'])->toBe('222222');
    });
});

