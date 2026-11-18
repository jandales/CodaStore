<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Mail\Orders\OrderConfirmedMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderReturn;
use App\Models\ShippingMethod;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class CashOnDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;
    private $product;
    private $method;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->gateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->gateway);

        $this->customer = $this->signInCustomer();
        $this->product = $this->createProduct(5, true, ['regular_price' => 500]);
        $this->method = ShippingMethod::factory()->create(['amount' => 150]);
        $this->fillBag();
    }

    /** 2 x 500 + 150 shipping = 1,150 */
    private function fillBag(int $qty = 2): void
    {
        $cart = Cart::factory()->create(['cart_id' => 555555555]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $this->product->id, 'qty' => $qty, 'attributes' => ['size' => 'M']]);

        $this->withCookie('cart-id', '555555555')->withSession([
            'email' => 'buyer@example.test',
            'shipping_method' => ['id' => $this->method->id, 'name' => $this->method->name, 'amount' => 150],
            'shipping_address' => [
                'firstname' => 'Test', 'lastname' => 'Buyer', 'street' => '1 Main St', 'city' => 'Quezon City',
                'phone' => '0917', 'country' => 'Philippines', 'region' => 'NCR', 'zipcode' => '1100',
            ],
        ]);
    }

    private function settings(array $values): void
    {
        GeneralSetting::first()->update($values);
    }

    private function placeCod()
    {
        return $this->post(route('checkout.place'), ['payment_method' => 'cod']);
    }

    private function stock(): int
    {
        return (int) Stock::where('product_id', $this->product->id)->value('qty');
    }

    // ---------------------------------------------------------------- placing orders

    public function test_payment_step_offers_cash_on_delivery()
    {
        $this->get(route('checkout.payment'))->assertOk()
            ->assertSee('Cash on delivery')
            ->assertSee('name="payment_method" value="cod"', false)
            ->assertSee('Place order');
    }

    public function test_placing_a_cod_order_creates_it_straight_away_with_cash_due()
    {
        $this->placeCod()
            ->assertRedirect(route('checkout.completed'))
            ->assertSessionHas('cod_due', 1150.0);

        $order = Order::first();
        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);
        $this->assertSame(1150.0, (float) $order->gross_total);
        $this->assertDatabaseHas('order_payments', ['order_id' => $order->id, 'provider' => 'cod', 'status' => 'pending', 'reference_number' => 'COD-' . $order->order_number]);
        $this->assertSame(3, $this->stock(), 'stock reserved');
        $this->assertSame([], $this->gateway->created, 'PayPal not involved');
        $this->assertSame(0, Cart::count(), 'bag emptied');
        Mail::assertQueued(OrderConfirmedMail::class);
    }

    public function test_cod_fee_is_added_to_the_order()
    {
        $this->settings(['cod_fee' => 50]);

        $this->get(route('checkout.payment'))->assertSee('+₱50.00 fee');
        $this->placeCod();

        $order = Order::first();
        $this->assertSame(50.0, (float) $order->payment_fee);
        $this->assertSame(1200.0, (float) $order->gross_total);
        $this->assertSame(1200.0, (float) $order->payment->amount);
    }

    public function test_confirmation_email_and_thank_you_page_say_how_much_cash_to_have_ready()
    {
        $this->placeCod();
        $order = Order::first();

        $this->get(route('checkout.completed'))->assertSee('please have <strong>₱1,150.00</strong> ready', false);

        $mail = new OrderConfirmedMail($order);
        $this->app->forgetInstance('mail.manager');
        $this->app->forgetInstance('mailer');
        Mail::clearResolvedInstance('mail.manager');
        $this->assertStringContainsString('cash on delivery', $mail->render());
    }

    public function test_paypal_choice_still_goes_to_paypal()
    {
        $this->post(route('checkout.place'), ['payment_method' => 'paypal'])->assertRedirect('https://paypal.test/approve/PAY-1');
        $this->assertSame(0, Order::count());
    }

    // ---------------------------------------------------------------- rules

    public function test_orders_over_the_maximum_cannot_use_cod()
    {
        $this->settings(['cod_max_total' => 1000]);

        $this->get(route('checkout.payment'))->assertSee('Cash on delivery is available for orders up to ₱1,000.00.');
        $this->placeCod()->assertRedirect(route('checkout.payment'))->assertSessionHas('error', 'Cash on delivery is available for orders up to ₱1,000.00.');
        $this->assertSame(0, Order::count());
    }

    public function test_cod_can_be_limited_to_some_shipping_methods()
    {
        $other = ShippingMethod::factory()->create(['name' => 'Express']);
        $this->settings(['cod_shipping_methods' => [$other->id]]);

        $this->placeCod()->assertSessionHas('error', 'Cash on delivery is not available for the shipping method you chose.');
        $this->assertSame(0, Order::count());
    }

    public function test_unpaid_cod_orders_per_customer_are_limited()
    {
        $this->settings(['cod_max_open_orders' => 1]);
        $this->placeCod();
        $this->fillBag(1);

        $this->placeCod()->assertSessionHas('error');
        $this->assertSame(1, Order::count());
    }

    public function test_switched_off_cod_is_hidden_and_refused()
    {
        $this->settings(['cod_enabled' => false]);

        $this->get(route('checkout.payment'))->assertDontSee('name="payment_method" value="cod"', false);
        $this->placeCod()->assertSessionHas('error', 'Cash on delivery is not available right now.');
        $this->assertSame(0, Order::count());
    }

    public function test_cod_needs_a_verified_email()
    {
        $this->signInCustomer(User::factory()->unverified()->create());

        $this->placeCod()->assertRedirect(route('verification.notice'));
        $this->assertSame(0, Order::count());
    }

    // ---------------------------------------------------------------- after the order

    public function test_cancelling_before_shipping_restores_stock_and_attempts_no_refund()
    {
        $this->placeCod();
        $order = Order::first();

        $this->put(route('orders.cancel', [$order->encryptedId()]))->assertSessionHas('success');

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(OrderPayment::STATUS_CANCELLED, $order->payment->fresh()->status);
        $this->assertSame(5, $this->stock());
        $this->assertSame([], $this->gateway->refunds);
    }

    public function test_marking_delivered_records_the_cash_as_collected()
    {
        $this->placeCod();
        $order = Order::first();
        $this->signInAdmin();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'shipped', 'courier' => 'lbc', 'tracking_number' => 'LBC1']);
        $this->get(route('admin.orders.show', [$order->encryptedId()]))->assertSee('Cash to collect')->assertSee('Not collected / returned to us');
        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'completed']);

        $payment = $order->payment->fresh();
        $this->assertSame(OrderPayment::STATUS_PAID, $payment->status);
        $this->assertNotNull($payment->collected_at);
    }

    public function test_not_collected_returns_the_order_and_restores_stock()
    {
        $this->placeCod();
        $order = Order::first();
        $this->signInAdmin();
        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'shipped', 'courier' => 'lbc', 'tracking_number' => 'LBC1']);

        $this->put(route('admin.orders.cod.not-collected', [$order->encryptedId()]))->assertSessionHas('success');

        $this->assertSame(Order::STATUS_RETURNED, $order->fresh()->status);
        $this->assertSame(OrderPayment::STATUS_FAILED, $order->payment->fresh()->status);
        $this->assertSame(5, $this->stock());

        // Can't be done twice.
        $this->put(route('admin.orders.cod.not-collected', [$order->encryptedId()]))->assertSessionHas('error');
        $this->assertSame(5, $this->stock());
    }

    public function test_returned_cod_order_needs_a_manual_refund()
    {
        $this->placeCod();
        $order = Order::first();
        $order->update(['status' => Order::STATUS_COMPLETED, 'delivered_at' => now()]);
        $order->payment->update(['status' => OrderPayment::STATUS_PAID]);
        OrderReturn::create(['order_id' => $order->id, 'user_id' => $order->user_id, 'reason' => 'wrong-size', 'status' => 'requested']);
        $this->signInAdmin();

        $this->put(route('admin.orders.return.approve', [$order->encryptedId()]), ['restock' => 1])
            ->assertSessionHas('success');

        $this->assertSame(OrderPayment::STATUS_REFUND_PENDING, $order->payment->fresh()->status);
        $this->assertSame([], $this->gateway->refunds, 'nothing sent through PayPal');
        $this->get(route('admin.orders.show', [$order->encryptedId()]))->assertSee('Refund to send')->assertSee('Refund sent');

        $this->put(route('admin.orders.cod.refund-sent', [$order->encryptedId()]))->assertSessionHas('success');
        $payment = $order->payment->fresh();
        $this->assertSame(OrderPayment::STATUS_REFUNDED, $payment->status);
        $this->assertNotNull($payment->refunded_at);
    }

    public function test_recording_a_refund_needs_the_refunds_permission()
    {
        $this->placeCod();
        $order = Order::first();
        $order->payment->update(['status' => OrderPayment::STATUS_REFUND_PENDING]);

        $this->signInAdmin($this->makeAdmin(['role' => 2, 'permissions' => ['orders']]));
        $this->put(route('admin.orders.cod.refund-sent', [$order->encryptedId()]))->assertForbidden();
        $this->assertSame(OrderPayment::STATUS_REFUND_PENDING, $order->payment->fresh()->status);
    }

    // ---------------------------------------------------------------- admin

    public function test_admin_changes_cod_settings()
    {
        $this->signInAdmin();

        $this->put(route('admin.setting.payments.update'), [
            'cod_enabled' => 1, 'cod_fee' => '40', 'cod_max_total' => '3000', 'cod_max_open_orders' => 3,
            'cod_shipping_methods' => [$this->method->id],
        ])->assertSessionHas('success');

        $settings = GeneralSetting::first();
        $this->assertTrue($settings->cod_enabled);
        $this->assertSame(40.0, (float) $settings->cod_fee);
        $this->assertSame(3000.0, (float) $settings->cod_max_total);
        $this->assertSame(3, (int) $settings->cod_max_open_orders);
        $this->assertSame([$this->method->id], $settings->cod_shipping_methods);

        $this->put(route('admin.setting.payments.update'), ['cod_fee' => '0', 'cod_max_total' => '', 'cod_max_open_orders' => 0]);
        $settings->refresh();
        $this->assertFalse($settings->cod_enabled);
        $this->assertNull($settings->cod_max_total);
        $this->assertNull($settings->cod_shipping_methods);

        $this->signInAdmin($this->makeAdmin(['role' => 2, 'permissions' => ['orders']]));
        $this->get(route('admin.setting.payments'))->assertForbidden();
    }

    public function test_payments_page_shows_cash_awaiting_collection_and_labels()
    {
        $this->placeCod();
        $this->signInAdmin();

        $this->get(route('admin.payments'))->assertOk()
            ->assertSee('Cash awaiting collection')
            ->assertSee('₱1,150.00')
            ->assertSee('Cash due on delivery');
        $this->get(route('admin.payments', ['method' => 'paypal']))->assertDontSee('Cash due on delivery');
    }

    public function test_invoice_and_packing_slip_show_cash_due()
    {
        $this->placeCod();
        $order = Order::first();
        $this->signInAdmin();

        $this->get(route('admin.orders.invoice', [$order->encryptedId()]))->assertSee('Cash on delivery · ₱1,150.00 due');
        $this->get(route('admin.orders.packing-slip', [$order->encryptedId()]))->assertSee('Cash on delivery: collect ₱1,150.00');
    }
}
