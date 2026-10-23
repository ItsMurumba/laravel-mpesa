<?php

namespace Itsmurumba\Mpesa\Tests;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Orchestra;
use Itsmurumba\Mpesa\MpesaServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Fakes Daraja: the OAuth endpoint returns a token, every other endpoint returns
     * `{"ok": true}`, and any unfaked request fails the test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            '*/oauth/v1/generate*' => Http::response(['access_token' => 'test-token', 'expires_in' => 3599], 200),
            '*' => Http::response(['ok' => true], 200),
        ]);
        Http::preventStrayRequests();
    }

    protected function getPackageProviders($app)
    {
        return [
            MpesaServiceProvider::class,
        ];
    }

    /**
     * Sets base Mpesa config and supports both legacy single-config and profiles config.
     */
    protected function defineEnvironment($app)
    {
        $base = [
            'consumerKey' => 'key',
            'consumerSecret' => 'secret',
            'callBackURL' => 'https://example.test/callback',
            'baseUrl' => 'https://example.test',
            'paybillNumber' => '123456',
            'lipaNaMpesaShortcode' => '654321',
            'lipaNaMpesaCallbackURL' => 'https://example.test/stk-callback',
            'lipaNaMpesaPasskey' => 'passkey',
            'confirmationURL' => 'https://example.test/confirm',
            'validationURL' => 'https://example.test/validate',
            'initiatorUsername' => 'initiator',
            'initiatorPassword' => 'password',
            'environment' => 'sandbox',
            'queueTimeOutURL' => 'https://example.test/timeout',
            'resultURL' => 'https://example.test/result',
        ];

        $app['config']->set('mpesa', array_merge($base, [
            'default_profile' => 'default',
            'profiles' => [
                'default' => $base,
            ],
        ]));
    }
}
