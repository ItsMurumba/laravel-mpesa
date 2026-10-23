<?php

use Itsmurumba\Mpesa\MpesaManager;

describe('Initiator credentials via for() overrides', function () {
    it('uses the overridden initiator and security credential for reversals', function () {
        (new MpesaManager())->for('tenant-x', [
            'initiatorUsername' => 'tenant-operator',
            'securityCredential' => 'pre-encrypted-credential',
        ])->reversals('QKA1B2C3D4', 100, '600000', '11', 'Refund');

        $payload = darajaRequests()[0]->data();
        expect(darajaRequests()[0]->url())->toBe('https://example.test/mpesa/reversal/v1/request');
        expect($payload['Initiator'])->toBe('tenant-operator');
        expect($payload['SecurityCredential'])->toBe('pre-encrypted-credential');
    });

    it('uses the overridden initiator and security credential for b2c payments', function () {
        (new MpesaManager())->for('tenant-x', [
            'initiatorUsername' => 'tenant-operator',
            'securityCredential' => 'pre-encrypted-credential',
        ])->b2cPayment('BusinessPayment', 100, '254700000000', 'Refund');

        $payload = darajaRequests()[0]->data();
        expect($payload['InitiatorName'])->toBe('tenant-operator');
        expect($payload['SecurityCredential'])->toBe('pre-encrypted-credential');
    });

    it('encrypts the overridden initiator password when no security credential is given', function () {
        (new MpesaManager())->for('tenant-x', [
            'initiatorPassword' => 'tenant-password',
        ])->reversals('QKA1B2C3D4', 100, '600000', '11', 'Refund');

        $credential = darajaRequests()[0]->data()['SecurityCredential'];
        expect(base64_decode($credential, true))->not->toBeFalse()->not->toBeEmpty();
    });
});
