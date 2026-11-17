<?php

namespace Tests\Unit;

use App\Services\Payments\PaymentException;
use App\Services\Payments\PayPalGateway;
use Tests\TestCase;

class PayPalGatewayTest extends TestCase
{
    public function test_missing_credentials_give_a_friendly_error_instead_of_a_crash()
    {
        config(['paypal.mode' => 'sandbox', 'paypal.sandbox.client_id' => '', 'paypal.sandbox.client_secret' => '']);

        $this->assertFalse(PayPalGateway::isConfigured());
        $this->expectException(PaymentException::class);
        $this->expectExceptionMessage('Online payment is not available right now. Please try again later.');

        (new PayPalGateway)->createPayment(100, 'PHP', 'https://x.test/r', 'https://x.test/c', 'REF');
    }

    public function test_refund_without_credentials_reports_failure()
    {
        config(['paypal.mode' => 'sandbox', 'paypal.sandbox.client_id' => '', 'paypal.sandbox.client_secret' => '']);

        $this->assertFalse((new PayPalGateway)->refund('CAP-1', 100, 'PHP', 'test'));
    }
}
