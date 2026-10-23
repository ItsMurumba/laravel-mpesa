<?php

use Itsmurumba\Mpesa\Mpesa;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Factory as HttpFactory;
use Itsmurumba\Mpesa\Exceptions\IsNullException;
use Itsmurumba\Mpesa\Exceptions\MpesaException;

describe('Http client + auth', function () {
    it('throws when setHttpResponse method is null', function () {
        $mpesa = new Mpesa();

        callPrivate($mpesa, 'setHttpResponse', ['/anything', null, []]);
    })->throws(IsNullException::class, 'Empty method not allowed');

    it('does not call Safaricom when constructed', function () {
        new Mpesa();

        Http::assertNothingSent();
    });

    it('sends the access token as a bearer token', function () {
        (new Mpesa())->expressPaymentQuery('ws_CO_1');

        Http::assertSent(fn (Request $request) => str_contains($request->url(), '/mpesa/stkpushquery/v1/query')
            && $request->hasHeader('Authorization', 'Bearer test-token'));
    });

    it('fetches the access token once per instance while it is valid', function () {
        $mpesa = new Mpesa();
        $mpesa->expressPaymentQuery('ws_CO_1');
        $mpesa->expressPaymentQuery('ws_CO_2');

        expect(Http::recorded(fn (Request $request) => str_contains($request->url(), '/oauth/')))->toHaveCount(1);
    });

    it('throws a clear exception and sends nothing when the token cannot be obtained', function () {
        $factory = new HttpFactory();
        $factory->fake([
            '*/oauth/v1/generate*' => Http::response(['errorMessage' => 'Invalid credentials'], 400),
            '*' => Http::response(['ok' => true], 200),
        ]);
        swapHttpFactory($factory);

        $mpesa = new Mpesa();

        expect(fn () => $mpesa->expressPayment(10, '254700000000'))
            ->toThrow(MpesaException::class, 'Could not obtain an M-Pesa access token (HTTP 400).');
        expect(darajaRequests())->toBeEmpty();
    });

    it('throws when the token response has no access token', function () {
        $factory = new HttpFactory();
        $factory->fake(['*' => Http::response(['unexpected' => true], 200)]);
        swapHttpFactory($factory);

        (new Mpesa())->expressPayment(10, '254700000000');
    })->throws(MpesaException::class, 'Could not obtain an M-Pesa access token');

    it('returns Daraja error responses as the raw body instead of throwing', function () {
        $factory = new HttpFactory();
        $factory->fake([
            '*/oauth/v1/generate*' => Http::response(['access_token' => 'test-token', 'expires_in' => 3599], 200),
            '*' => Http::response(['errorCode' => '400.002.02', 'errorMessage' => 'Bad Request - Invalid Amount'], 400),
        ]);
        swapHttpFactory($factory);

        $response = (new Mpesa())->expressPayment(0, '254700000000');

        expect(json_decode($response, true)['errorCode'])->toBe('400.002.02');
    });
});
