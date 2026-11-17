<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Mail\Orders\ReturnApprovedMail;
use App\Mail\Orders\ReturnRejectedMail;
use App\Mail\Orders\ReturnRequestedMail;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderReturn;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class ReturnsTest extends TestCase
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

    private function deliveredOrder(?User $user = null, array $attributes = []): Order
    {
        $order = $this->createPaidOrder($user, array_merge(['status' => Order::STATUS_COMPLETED, 'delivered_at' => now()->subDays(3)], $attributes), 5);
        GeneralSetting::first()->update(['site_email' => 'store@coda.test']);

        return $order;
    }

    private function requestReturn(Order $order, array $data = ['reason' => 'wrong-size'])
    {
        return $this->from(route('orders.details', [$order->encryptedId()]))
            ->post(route('orders.return', [$order->encryptedId()]), $data);
    }

    public function test_customer_can_request_a_return_on_a_delivered_order()
    {
        $user = User::factory()->create();
        $order = $this->deliveredOrder($user);
        $this->signInCustomer($user);

        $this->get(route('orders.details', [$order->encryptedId()]))->assertSee('Return this order');

        $this->requestReturn($order, ['reason' => 'damaged', 'details' => 'Seam split'])
            ->assertSessionHas('success');

        $return = $order->returnRequest()->first();
        $this->assertSame(OrderReturn::STATUS_REQUESTED, $return->status);
        $this->assertSame('Seam split', $return->details);
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status, 'nothing changes until the store decides');
        Mail::assertQueued(ReturnRequestedMail::class, fn ($mail) => $mail->hasTo('store@coda.test'));

        $this->get(route('orders.details', [$order->encryptedId()]))
            ->assertSee('Return requested on')
            ->assertDontSee('Return this order');
    }

    public function test_return_needs_a_valid_reason_and_details_for_other()
    {
        $user = User::factory()->create();
        $order = $this->deliveredOrder($user);
        $this->signInCustomer($user);

        $this->requestReturn($order, ['reason' => 'nope'])->assertSessionHasErrors('reason');
        $this->requestReturn($order, ['reason' => 'other'])->assertSessionHasErrors('details');
        $this->assertNull($order->returnRequest()->first());
    }

    public function test_only_delivered_orders_inside_the_window_can_be_returned()
    {
        $user = User::factory()->create();
        $shipped = $this->deliveredOrder($user, ['status' => Order::STATUS_SHIPPED]);
        $old = $this->deliveredOrder($user, ['delivered_at' => now()->subDays(config('store.return_days') + 1)]);
        $this->signInCustomer($user);

        $this->requestReturn($shipped)->assertSessionHas('error');
        $this->requestReturn($old)->assertSessionHas('error');
        $this->assertSame(0, OrderReturn::count());
        $this->get(route('orders.details', [$old->encryptedId()]))->assertDontSee('Return this order');
    }

    public function test_one_return_request_per_order_and_only_the_owner()
    {
        $user = User::factory()->create();
        $order = $this->deliveredOrder($user);

        $this->signInCustomer();
        $this->requestReturn($order)->assertForbidden();

        $this->signInCustomer($user);
        $this->requestReturn($order)->assertSessionHas('success');
        $this->requestReturn($order)->assertSessionHas('error');
        $this->assertSame(1, OrderReturn::count());
    }

    public function test_admin_approves_refunds_restocks_and_emails()
    {
        $user = User::factory()->create();
        $order = $this->deliveredOrder($user);
        $this->signInCustomer($user);
        $this->requestReturn($order);
        $productId = $order->items->first()->product_id;

        $this->signInAdmin();
        $this->get(route('admin.orders.returns'))->assertOk()->assertSee($order->order_number);
        $this->get(route('admin.orders.show', [$order->encryptedId()]))->assertSee('Approve and refund');

        $this->put(route('admin.orders.return.approve', [$order->encryptedId()]), ['restock' => 1])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(Order::STATUS_RETURNED, $order->status);
        $this->assertNotNull($order->returned_at);
        $this->assertSame(OrderReturn::STATUS_REFUNDED, $order->returnRequest->status);
        $this->assertTrue($order->returnRequest->restocked);
        $this->assertSame(7, Stock::where('product_id', $productId)->value('qty'), '5 + 2 returned');
        $this->assertSame(OrderPayment::STATUS_REFUNDED, $order->payment->status);
        $this->assertSame([['captureId' => 'CAP-123', 'amount' => 1150.0, 'currency' => 'PHP', 'note' => "Order {$order->order_number} returned"]], $this->gateway->refunds);
        Mail::assertQueued(ReturnApprovedMail::class, fn ($mail) => $mail->hasTo('ana@example.test'));

        // A second approval does nothing.
        $this->put(route('admin.orders.return.approve', [$order->encryptedId()]), ['restock' => 1])->assertSessionHas('error');
        $this->assertCount(1, $this->gateway->refunds);
        $this->assertSame(7, Stock::where('product_id', $productId)->value('qty'));
    }

    public function test_approve_without_restock_and_a_failed_refund_is_reported()
    {
        $user = User::factory()->create();
        $order = $this->deliveredOrder($user);
        $this->signInCustomer($user);
        $this->requestReturn($order);
        $this->gateway->refundSucceeds = false;

        $this->signInAdmin();
        $this->put(route('admin.orders.return.approve', [$order->encryptedId()]))
            ->assertSessionHas('error', 'Return approved, but the PayPal refund failed. Refund it by hand in PayPal.');

        $this->assertSame(5, Stock::where('product_id', $order->items->first()->product_id)->value('qty'));
        $this->assertSame(OrderPayment::STATUS_REFUND_FAILED, $order->payment->fresh()->status);
        $this->assertSame(Order::STATUS_RETURNED, $order->fresh()->status);
    }

    public function test_admin_rejects_with_a_note()
    {
        $user = User::factory()->create();
        $order = $this->deliveredOrder($user);
        $this->signInCustomer($user);
        $this->requestReturn($order);

        $this->signInAdmin();
        $this->put(route('admin.orders.return.reject', [$order->encryptedId()]), [])->assertSessionHasErrors('admin_note');
        $this->put(route('admin.orders.return.reject', [$order->encryptedId()]), ['admin_note' => 'Item was worn.'])
            ->assertSessionHas('success');

        $this->assertSame(OrderReturn::STATUS_REJECTED, $order->returnRequest()->first()->status);
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertSame([], $this->gateway->refunds);
        Mail::assertQueued(ReturnRejectedMail::class, fn ($mail) => $mail->hasTo('ana@example.test'));

        $this->signInCustomer($user);
        $this->get(route('orders.details', [$order->encryptedId()]))->assertSee('Item was worn.');
    }
}
