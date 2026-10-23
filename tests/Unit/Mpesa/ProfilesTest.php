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

        $manager->for('default')->expressPayment(10, '254700000000');
        $manager->for('tenant_b')->expressPayment(10, '254700000000');

        $payloadA = darajaRequests()[0]->data();
        $payloadB = darajaRequests()[1]->data();

        expect($payloadA['BusinessShortCode'])->toBe('111111');
        expect($payloadB['BusinessShortCode'])->toBe('222222');
    });
});

