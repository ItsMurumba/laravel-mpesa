<?php

namespace Itsmurumba\Mpesa\Tests\Unit;

use Itsmurumba\Mpesa\Facades\Mpesa;
use Itsmurumba\Mpesa\Tests\TestCase;

class MpesaAuthTest extends TestCase
{

    /**
     * @test
     */
    public function can_get_access_token()
    {
        $response = $this->mpesa->getAccessToken();
        $this->assertTrue($response);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testGetAccessTokenReturnsString()
    {
        $token = $this->mpesa->getAccessToken();

        $this->assertIsString($token);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testGetAccessTokenSetsAccessTokenAndExpiresIn()
    {
        $token = $this->mpesa->getAccessToken();

        $this->assertNotEmpty($this->mpesa->accessToken);
        $this->assertNotEmpty($this->mpesa->expiresIn);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testGetAccessTokenReturnsFalseOnError()
    {
        $this->baseUrl = 'https://invalidurl.com';

        $result = $this->getAccessToken();

        $this->assertFalse($result);
    }
}
