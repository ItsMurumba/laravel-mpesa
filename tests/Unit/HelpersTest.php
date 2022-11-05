<?php

namespace Itsmurumba\Mpesa\Tests\Unit;

use Mockery;
use Itsmurumba\Mpesa\Tests\TestCase;

class HelpersTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_instance_of_mpesa()
    {
        $this->assertInstanceOf("Itsmurumba\Mpesa\Mpesa", $this->mpesa);
    }
}
