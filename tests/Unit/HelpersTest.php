<?php

namespace Itsmurumba\Mpesa\Tests\Unit;

use Mockery;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    protected $mpesa;

    public function setUp(): void
    {
        $this->mpesa = Mockery::mock('Itsmurumba\Mpesa\Mpesa');
        $this->mock = Mockery::mock('GuzzleHttp\Client');
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * Test that helper returns correct
     *
     * @return void
     */
    function it_returns_instance_of_mpesa()
    {
        $this->assertInstanceOf("Itsmurumba\Mpesa\Mpesa", $this->mpesa);
    }
}
