<?php

namespace Itsmurumba\Mpesa\Tests;

use Mockery;
use Itsmurumba\Mpesa\MpesaServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    protected $mpesa;
    protected $mock;

    public function setUp(): void
    {
        parent::setUp();
        $this->mpesa = Mockery::mock('Itsmurumba\Mpesa\Mpesa');
        $this->mock = Mockery::mock('GuzzleHttp\Client');
    }

    protected function getPackageProviders($app)
    {
        return [
            MpesaServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
    }

    public function tearDown(): void
    {
        Mockery::close();
    }
}
