<?php

use Itsmurumba\Mpesa\MpesaManager;

describe('MpesaManager backwards compatibility', function () {
    it('forwards unknown calls to the default instance', function () {
        $manager = new class extends MpesaManager {
            public function defaultInstance()
            {
                return new class {
                    public array $calls = [];
                    public function expressPayment($amount, $phone)
                    {
                        $this->calls[] = [$amount, $phone];
                        return 'ok';
                    }
                };
            }
        };

        $result = $manager->expressPayment(10, '254700000000');

        expect($result)->toBe('ok');
    });
});

