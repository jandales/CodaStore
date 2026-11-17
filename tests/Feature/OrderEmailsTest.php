<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Mail\Orders\OrderCancelledMail;
use App\Mail\Orders\OrderConfirmedMail;
use App\Mail\Orders\OrderShippedMail;
use App\Models\Order;
use App\Models\OrderBilling;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\OrderShipping;
use App\Models\User;
use App\Services\OrderNotifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class OrderEmailsTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->gateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->gateway);
    }

    /** A paid PayPal order with shipping and billing details. */
    private function paidOrder(?User $user = null, array $attributes = []): Order
    {
        $user = $user ?: User::factory()->create();
        $product = $this->createProduct(10, true, ['name' => 'overshirt', 'regular_price' => 500]);
        $order = Order::factory()->create(array_merge(['user_id' => $user->id, 'gross_total' => 1150, 'net_total' => 1000, 'shipping_charge' => 150], $attributes));
        OrderProduct::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => 2, 'price' => 500, 'properties' => ['size' => 'M']]);
        OrderShipping::create(['order_id' => $order->id, 'firstname' => 'Ana', 'lastname' => 'Cruz', 'street' => '1 Main St', 'city' => 'Quezon City', 'phone' => '0917', 'country' => 'Philippines', 'region' => 'NCR', 'zipcode' => '1100']);
        OrderBilling::create(['order_id' => $order->id, 'firstname' => 'Ana', 'lastname' => 'Cruz', 'email' => 'ana@example.test', 'street' => '1 Main St', 'city' => 'Quezon City', 'phone' => '0917', 'country' => 'Philippines', 'region' => 'NCR', 'zipcode' => '1100']);
        OrderPayment::create(['order_id' => $order->id, 'provider' => 'paypal', 'reference_number' => 'CAP-123', 'amount' => '1150', 'status' => 'paid']);

        return $order->fresh();
    }

    /** Mail::fake() also replaces the mailer render() uses; restore the real one for content checks. */
    private function realRender($mail): string
    {
        $this->app->forgetInstance('mail.manager');
        $this->app->forgetInstance('mailer');
        Mail::clearResolvedInstance('mail.manager');

        return $mail->render();
    }

    public function test_admin_ship_saves_tracking_and_emails_the_customer()
    {
        $order = $this->paidOrder();
        $this->signInAdmin();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'shipped', 'courier' => 'lbc', 'tracking_number' => ' 1234-5678 '])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(Order::STATUS_SHIPPED, $order->status);
        $this->assertSame('lbc', $order->courier);
        $this->assertSame('1234-5678', $order->tracking_number);
        $this->assertNotNull($order->shipped_at);
        Mail::assertQueued(OrderShippedMail::class, fn ($mail) => $mail->hasTo('ana@example.test') && $mail->order->is($order));
    }

    public function test_shipping_requires_courier_and_tracking_number()
    {
        $order = $this->paidOrder();
        $this->signInAdmin();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'shipped'])
            ->assertSessionHasErrors(['courier' => 'Choose the courier for this shipment.', 'tracking_number' => 'Enter the tracking number from the courier.']);

        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);
        Mail::assertNothingQueued();
    }

    public function test_status_can_only_move_forward()
    {
        $order = $this->paidOrder(null, ['status' => Order::STATUS_COMPLETED]);
        $this->signInAdmin();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'shipped', 'courier' => 'lbc', 'tracking_number' => 'X'])
            ->assertSessionHasErrors('status');
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
    }

    public function test_cancel_refunds_the_paypal_payment_and_emails_the_customer()
    {
        $user = User::factory()->create();
        $order = $this->paidOrder($user);
        $this->signInCustomer($user);

        $this->put(route('orders.cancel', [$order->encryptedId()]))->assertSessionHas('success');

        $this->assertSame([['captureId' => 'CAP-123', 'amount' => 1150.0, 'currency' => 'PHP', 'note' => "Order {$order->order_number} cancelled"]], $this->gateway->refunds);
        $this->assertSame(OrderPayment::STATUS_REFUNDED, $order->payment->fresh()->status);
        Mail::assertQueued(OrderCancelledMail::class, fn ($mail) => $mail->hasTo('ana@example.test'));
    }

    public function test_failed_refund_is_recorded_and_the_customer_is_still_emailed()
    {
        $this->gateway->refundSucceeds = false;
        $user = User::factory()->create();
        $order = $this->paidOrder($user);
        $this->signInCustomer($user);

        $this->put(route('orders.cancel', [$order->encryptedId()]));

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status, 'cancellation stands');
        $this->assertSame(OrderPayment::STATUS_REFUND_FAILED, $order->payment->fresh()->status);
        Mail::assertQueued(OrderCancelledMail::class);
    }

    public function test_admin_cancel_also_refunds_and_emails()
    {
        $order = $this->paidOrder();
        $this->signInAdmin();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'cancelled']);

        $this->assertCount(1, $this->gateway->refunds);
        Mail::assertQueued(OrderCancelledMail::class);
    }

    public function test_confirmation_email_content()
    {
        $order = $this->paidOrder();

        $html = $this->realRender(new OrderConfirmedMail($order));

        $this->assertStringContainsString((string) $order->order_number, $html);
        $this->assertStringContainsString('Overshirt (Size: M)', $html);
        $this->assertStringContainsString('₱1,150.00', $html);
        $this->assertStringContainsString('Quezon City', $html);
    }

    public function test_shipped_email_shows_courier_and_tracking_number()
    {
        $order = $this->paidOrder(null, ['status' => Order::STATUS_SHIPPED, 'courier' => 'jnt', 'tracking_number' => 'JT0001']);

        $html = $this->realRender(new OrderShippedMail($order));

        $this->assertStringContainsString('J&amp;T Express', $html);
        $this->assertStringContainsString('JT0001', $html);
    }

    public function test_shipped_email_links_to_tracking_page_when_configured()
    {
        config(['couriers.lbc.url' => 'https://courier.test/track?no={number}']);
        $order = $this->paidOrder(null, ['status' => Order::STATUS_SHIPPED, 'courier' => 'lbc', 'tracking_number' => 'LBC 9']);

        $this->assertSame('https://courier.test/track?no=LBC%209', $order->trackingUrl());
        $this->assertStringContainsString('Track your parcel', $this->realRender(new OrderShippedMail($order)));
    }

    public function test_cancelled_email_explains_the_refund()
    {
        $order = $this->paidOrder(null, ['status' => Order::STATUS_CANCELLED, 'cancelled_at' => now()]);
        $order->payment->update(['status' => OrderPayment::STATUS_REFUNDED]);

        $this->assertStringContainsString('refunded', $this->realRender(new OrderCancelledMail($order->fresh())));
    }

    public function test_customer_order_page_shows_tracking()
    {
        $this->seedStoreBasics();
        $user = User::factory()->create();
        $order = $this->paidOrder($user, ['status' => Order::STATUS_SHIPPED, 'courier' => 'ninjavan', 'tracking_number' => 'NV42', 'shipped_at' => now()]);
        $this->signInCustomer($user);

        $this->get(route('orders.details', [$order->encryptedId()]))
            ->assertOk()
            ->assertSee('Ninja Van')
            ->assertSee('NV42');
    }

    public function test_a_mail_failure_never_breaks_the_action()
    {
        $order = $this->paidOrder();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        app(OrderNotifier::class)->shipped($order); // must not throw

        $this->assertTrue(true);
    }
}
