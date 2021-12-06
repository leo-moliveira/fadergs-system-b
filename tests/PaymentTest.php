<?php

use App\Http\Classes\MyPayment;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PaymentTest extends TestCase
{
    public function testPaymentClass()
    {
        $pay = new MyPayment();
        $pay->pay();
        $this->assertEquals(
            1,0
        );
    }
}
