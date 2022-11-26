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
}
