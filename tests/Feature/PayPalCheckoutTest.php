<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\ShippingMethod;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class PayPalCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private FakePaymentGateway $gateway;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->gateway);

        $this->signInCustomer();
        $this->product = $this->createProduct(5, true, ['regular_price' => 500]);

        $cart = Cart::factory()->create(['cart_id' => 555555555]);
        CartItem::factory()->create(['cart_id' => $cart->id, 'product_id' => $this->product->id, 'qty' => 2, 'attributes' => ['size' => 'M']]);
        $method = ShippingMethod::factory()->create(['amount' => 150]);

        $this->withCookie('cart-id', '555555555')->withSession([
            'email' => 'buyer@example.test',
            'shipping_method' => ['id' => $method->id, 'amount' => 150],
            'shipping_address' => [
                'firstname' => 'Test', 'lastname' => 'Buyer', 'street' => '1 Main St', 'city' => 'Quezon City',
                'phone' => '0917', 'country' => 'Philippines', 'region' => 'NCR', 'zipcode' => '1100',
            ],
        ]);
    }

    private function startPayment(array $billing = [])
    {
        return $this->post(route('checkout.paypal.start'), $billing);
    }

    private function returnFromPayPal(string $token = 'PAY-1')
    {
        return $this->get(route('checkout.paypal.return', ['token' => $token, 'PayerID' => 'PAYER']));
    }

    private function stock(): int
    {
        return (int) Stock::where('product_id', $this->product->id)->value('qty');
    }

    public function test_start_redirects_to_paypal_for_the_server_calculated_total()
    {
        $this->startPayment()->assertRedirect('https://paypal.test/approve/PAY-1');

        $this->assertSame(1150.0, $this->gateway->created['PAY-1']['amount']); // 2 x 500 + 150 shipping
        $this->assertSame('PHP', $this->gateway->created['PAY-1']['currency']);
        $this->assertSame(0, Order::count(), 'no order before payment is captured');
    }

    public function test_successful_capture_creates_exactly_one_paid_order()
    {
        $this->startPayment();

        $response = $this->returnFromPayPal();

        $order = Order::first();
        $response->assertRedirect(route('checkout.completed'));
        $response->assertSessionHas('order_number', $order->order_number);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);
        $this->assertSame(1150.0, (float) $order->gross_total);
        $this->assertDatabaseHas('order_payments', ['order_id' => $order->id, 'provider' => 'paypal', 'reference_number' => 'CAP-PAY-1', 'status' => 'paid']);
        $this->assertSame(3, $this->stock());
        $this->assertSame(0, CartItem::count(), 'bag is emptied');
    }

    public function test_refreshing_the_return_page_does_not_create_a_second_order()
    {
        $this->startPayment();
        $this->returnFromPayPal();

        $this->returnFromPayPal()->assertRedirect(route('checkout.completed'));

        $this->assertSame(1, Order::count());
        $this->assertCount(1, $this->gateway->captured);
    }

    public function test_return_with_an_unknown_token_is_rejected()
    {
        $this->startPayment();

        $this->returnFromPayPal('PAY-999')->assertRedirect(route('checkout.payment'))->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertCount(0, $this->gateway->captured);
    }

    public function test_failed_capture_creates_no_order()
    {
        $this->gateway->captureCompletes = false;
        $this->startPayment();

        $this->returnFromPayPal()->assertRedirect(route('checkout.payment'))->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertSame(5, $this->stock());
    }

    public function test_payment_is_refunded_if_the_item_sells_out_while_paying()
    {
        $this->startPayment();
        Stock::where('product_id', $this->product->id)->update(['qty' => 1]); // someone else bought it

        $this->returnFromPayPal()->assertRedirect(route('cart'))->assertSessionHas('error');

        $this->assertSame(0, Order::count());
        $this->assertCount(1, $this->gateway->refunds);
        $this->assertSame('CAP-PAY-1', $this->gateway->refunds[0]['captureId']);
        $this->assertSame(1150.0, $this->gateway->refunds[0]['amount']);
    }

    public function test_failed_automatic_refund_is_not_reported_as_refunded()
    {
        $this->gateway->refundSucceeds = false;
        $this->startPayment();
        Stock::where('product_id', $this->product->id)->update(['qty' => 0]);

        $this->returnFromPayPal()->assertSessionHas('error', fn ($m) => str_contains($m, "couldn't refund your payment automatically"));
        $this->assertSame(0, Order::count());
    }

    public function test_payment_is_refunded_if_paypal_captured_a_different_amount()
    {
        $this->gateway->captureAmountOverride = 1.00;
        $this->startPayment();

        $this->returnFromPayPal()->assertRedirect(route('cart'));

        $this->assertSame(0, Order::count());
        $this->assertCount(1, $this->gateway->refunds);
    }

    public function test_start_with_out_of_stock_bag_never_reaches_paypal()
    {
        Stock::where('product_id', $this->product->id)->update(['qty' => 1]);

        $this->startPayment()->assertRedirect(route('cart'))->assertSessionHas('error');

        $this->assertCount(0, $this->gateway->created);
    }

    public function test_paypal_error_on_start_returns_to_payment_step()
    {
        $this->gateway->failCreate = true;

        $this->startPayment()->assertRedirect(route('checkout.payment'))->assertSessionHas('error');
    }

    public function test_cancel_returns_to_payment_step_with_the_bag_intact()
    {
        $this->startPayment();

        $this->get(route('checkout.paypal.cancel'))->assertRedirect(route('checkout.payment'))->assertSessionHas('message');

        $this->assertSame(1, CartItem::count());
        $this->assertSame(0, Order::count());
    }

    public function test_different_billing_address_is_validated_and_saved()
    {
        $this->startPayment(['is_new_billing' => 1])->assertSessionHasErrors(['billing_firstname', 'billing_city']);

        $billing = [
            'is_new_billing' => 1, 'billing_firstname' => 'Bill', 'billing_lastname' => 'Payer', 'billing_street' => '9 Side St',
            'billing_city' => 'Makati', 'billing_phone' => '0918', 'billing_country' => 'Philippines', 'billing_region' => 'NCR', 'billing_zipcode' => '1200',
        ];
        $this->startPayment($billing)->assertRedirect('https://paypal.test/approve/PAY-1');
        $this->returnFromPayPal();

        $this->assertDatabaseHas('order_billings', ['firstname' => 'Bill', 'city' => 'Makati']);
    }

    public function test_old_unpaid_pay_now_route_is_gone()
    {
        $this->post('/pay-now/store')->assertNotFound();
    }
}
