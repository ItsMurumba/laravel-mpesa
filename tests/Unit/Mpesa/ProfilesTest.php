<?php

use Illuminate\Support\Facades\Http;
use Itsmurumba\Mpesa\Exceptions\MpesaException;
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

    it('refuses an unknown profile instead of using the default credentials', function () {
        expect(fn () => (new MpesaManager())->for('typo'))
            ->toThrow(MpesaException::class, 'M-Pesa profile [typo] was not found.');
        expect(Http::recorded())->toBeEmpty();
    });

    it('still takes an unknown profile name when the credentials come as overrides', function () {
        (new MpesaManager())->for('tenant-x', ['lipaNaMpesaShortcode' => '333333'])
            ->expressPayment(10, '254700000000');

        expect(darajaRequests()[0]->data()['BusinessShortCode'])->toBe('333333');
    });

    it('resolves the default profile when none is configured', function () {
        config()->set('mpesa.profiles', []);

        (new MpesaManager())->for('default')->expressPayment(10, '254700000000');

        expect(darajaRequests()[0]->data()['BusinessShortCode'])->toBe('654321');
    });
});

