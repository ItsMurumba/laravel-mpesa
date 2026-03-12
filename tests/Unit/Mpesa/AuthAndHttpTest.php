<?php

use Itsmurumba\Mpesa\Mpesa;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Factory as HttpFactory;
use Itsmurumba\Mpesa\Exceptions\IsNullException;

describe('Http client + auth', function () {
    it('throws when setHttpResponse method is null', function () {
        $mpesa = new Mpesa();

        callPrivate($mpesa, 'setHttpResponse', ['/anything', null, []]);
    })->throws(IsNullException::class, 'Empty method not allowed');

    it('returns false when access token endpoint returns non-200', function () {
        $factory = new HttpFactory();
        $factory->fake([
            '*' => Http::response(['error' => 'nope'], 500),
        ]);
        swapHttpFactory($factory);

        $mpesa = (new ReflectionClass(Mpesa::class))->newInstanceWithoutConstructor();
        setProtected($mpesa, 'consumerKey', 'key');
        setProtected($mpesa, 'consumerSecret', 'secret');
        setProtected($mpesa, 'baseUrl', 'https://example.test');

        $token = callPrivate($mpesa, 'getAccessToken');

        expect($token)->toBeFalse();
    });
});

