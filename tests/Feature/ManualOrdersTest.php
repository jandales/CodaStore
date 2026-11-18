<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Mail\Orders\OrderConfirmedMail;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\OrderNote;
use App\Models\OrderPayment;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Services\Products\ProductSkus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class ManualOrdersTest extends TestCase
{
    use RefreshDatabase;

    private $tee;      // sizes S (5 left), M (2 left, ₱650)
    private $cap;      // no variants, 10 left, ₱300
    private $method;
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->app->instance(PaymentGateway::class, new FakePaymentGateway);

        $this->tee = $this->createProduct(0, true, ['name' => 'linen tee', 'regular_price' => 500, 'sku' => 'TEE']);
        app(ProductSkus::class)->sync($this->tee, [
            ['options' => ['size' => 'S'], 'qty' => 5],
            ['options' => ['size' => 'M'], 'qty' => 2, 'price' => 650],
        ]);
        $this->tee = $this->tee->fresh();
        $this->cap = $this->createProduct(10, false, ['name' => 'cap', 'regular_price' => 300, 'sku' => 'CAP']);
        $this->method = ShippingMethod::factory()->create(['amount' => 150]);
        $this->admin = $this->signInAdmin();
    }

    private function sku(string $size)
    {
        return $this->tee->skuFor(['size' => $size])->fresh();
    }

    private function form(array $overrides = []): array
    {
        return array_merge([
            'customer_mode' => 'new',
            'email' => '',
            'firstname' => 'Ana', 'lastname' => 'Cruz', 'phone' => '09171234567',
            'street' => '1 Main St', 'city' => 'Quezon City', 'region' => 'NCR', 'zipcode' => '1100', 'country' => 'Philippines',
            'lines' => [
                ['product_id' => $this->tee->id, 'sku_id' => $this->sku('M')->id, 'qty' => 1],
                ['product_id' => $this->cap->id, 'qty' => 2],
            ],
            'shipping_method_id' => $this->method->id,
            'shipping_charge' => 100,
            'payment' => 'cod',
            'channel' => 'facebook',
            'order_date' => now()->toDateString(),
            'send_email' => 1,
        ], $overrides);
    }

    // ---------------------------------------------------------------- page and lookups

    public function test_create_order_page_and_lookups()
    {
        $this->get(route('admin.orders'))->assertSee(route('admin.orders.create'), false);
        $this->get(route('admin.orders.create'))->assertOk()
            ->assertSee('Create order')->assertSee('Facebook')->assertSee('Walk-in')->assertDontSee('value="website"', false);

        $products = $this->getJson(route('admin.orders.lookup.products', ['q' => 'tee']))->assertOk()->json('products');
        $this->assertSame('linen tee', $products[0]['name']);
        $this->assertSame([['S', 500, 5], ['M', 650, 2]], collect($products[0]['variants'])->map(fn ($v) => [$v['label'], (int) $v['price'], $v['qty']])->all());

        $user = User::factory()->create(['name' => 'Bea Santos', 'email' => 'bea@example.test']);
        $user->shippingAddress()->create(['firstname' => 'Bea', 'lastname' => 'Santos', 'street' => '2 Elm', 'city' => 'Pasig', 'phone' => '0918', 'country' => 'Philippines', 'region' => 'NCR', 'zipcode' => '1600', 'status' => 1]);
        $customers = $this->getJson(route('admin.orders.lookup.customers', ['q' => 'bea']))->json('customers');
        $this->assertSame('Pasig', $customers[0]['address']['city']);
    }

    // ---------------------------------------------------------------- creating orders

    public function test_guest_cod_order_takes_stock_and_is_priced_on_the_server()
    {
        $response = $this->post(route('admin.orders.manual.store'), $this->form(['note' => 'Asked for gift wrap']));

        $order = Order::first();
        $response->assertRedirect()->assertSessionHas('success', 'Order ' . $order->order_number . ' created.');
        $this->assertStringEndsWith('/show', $response->headers->get('Location'), 'opens the new order');
        $this->assertNull($order->user_id, 'guest order');
        $this->assertSame('Ana Cruz', $order->customerName());
        $this->assertSame('facebook', $order->channel);
        $this->assertSame($this->admin->id, (int) $order->created_by_admin_id);
        $this->assertSame(1350.0, (float) $order->gross_total, '650 + 2 x 300 + 100 shipping');
        $this->assertSame(1, $this->sku('M')->qty);
        $this->assertSame(8, $this->cap->fresh()->stockQty());
        $this->assertDatabaseHas('order_payments', ['order_id' => $order->id, 'provider' => 'cod', 'status' => 'pending']);
        $this->assertSame('Asked for gift wrap', OrderNote::where('order_id', $order->id)->value('body'));
        $this->assertStringContainsString('Created manual order (Facebook, ₱1,350.00)', ActivityLog::where('action', 'order.manual')->value('description'));
        Mail::assertNothingQueued(); // no email address

        $this->get(route('admin.orders.show', [$order->encryptedId()]))->assertOk()->assertSee('Facebook')->assertSee('(guest)');
        $this->get(route('admin.orders'))->assertSee('Ana Cruz')->assertSee('guest');
        $this->get(route('admin.orders.search', ['keyword' => '09171234567']))->assertSee($order->order_number);
        $this->get(route('admin.dashboard'))->assertOk()->assertSee('Ana Cruz');
    }

    public function test_new_customer_with_email_gets_an_account_and_a_confirmation()
    {
        $this->post(route('admin.orders.manual.store'), $this->form(['email' => 'Ana@Example.test']));

        $order = Order::first();
        $user = User::where('email', 'ana@example.test')->first();
        $this->assertSame($user->id, (int) $order->user_id);
        Mail::assertQueued(OrderConfirmedMail::class, fn ($mail) => $mail->hasTo('ana@example.test'));
    }

    public function test_existing_customer_and_paid_by_gcash()
    {
        $user = User::factory()->create(['email' => 'bea@example.test']);

        $this->post(route('admin.orders.manual.store'), $this->form([
            'customer_mode' => 'existing', 'user_id' => $user->id,
            'payment' => 'paid', 'payment_method' => 'gcash', 'payment_reference' => 'GC-778899',
            'send_email' => 0,
        ]));

        $order = Order::first();
        $this->assertSame($user->id, (int) $order->user_id);
        $this->assertDatabaseHas('order_payments', ['order_id' => $order->id, 'provider' => 'manual', 'status' => 'paid', 'method' => 'gcash', 'reference_number' => 'GC-778899']);
        $this->assertSame('GCash', $order->payment->methodLabel());
        Mail::assertNothingQueued();
    }

    public function test_discounts_are_capped_and_worked_out_on_the_server()
    {
        $this->post(route('admin.orders.manual.store'), $this->form(['discount_type' => 'percent', 'discount_value' => 10, 'shipping_charge' => 0]));
        $this->assertSame(1125.0, (float) Order::first()->gross_total, '1,250 - 10%');

        $this->post(route('admin.orders.manual.store'), $this->form(['discount_type' => 'amount', 'discount_value' => 99999, 'shipping_charge' => 50, 'lines' => [['product_id' => $this->cap->id, 'qty' => 1]]]));
        $last = Order::latest('id')->first();
        $this->assertSame(300.0, (float) $last->discount_amount, 'never more than the items');
        $this->assertSame(50.0, (float) $last->gross_total);
    }

    public function test_awaiting_payment_then_mark_as_paid()
    {
        $this->post(route('admin.orders.manual.store'), $this->form(['payment' => 'awaiting']));
        $order = Order::first();

        $this->get(route('admin.orders.awaiting'))->assertOk()->assertSee($order->order_number);
        $this->get(route('admin.orders.show', [$order->encryptedId()]))->assertSee('Mark as paid');

        $this->put(route('admin.orders.mark-paid', [$order->encryptedId()]), ['payment_method' => 'bank_transfer', 'payment_reference' => 'BDO-1'])
            ->assertSessionHas('success');

        $payment = $order->payment->fresh();
        $this->assertSame('paid', $payment->status);
        $this->assertSame('bank_transfer', $payment->method);
        $this->assertSame('BDO-1', $payment->reference_number);
        $this->assertNotNull($payment->collected_at);
        $this->get(route('admin.orders.awaiting'))->assertDontSee($order->order_number);
    }

    // ---------------------------------------------------------------- rules

    public function test_variants_stock_and_dates_are_checked()
    {
        // A product with variants needs one.
        $this->post(route('admin.orders.manual.store'), $this->form(['lines' => [['product_id' => $this->tee->id, 'qty' => 1]]]))
            ->assertSessionHasErrors('lines.0.sku_id');
        // A variant of another product is refused.
        $other = $this->createProduct(0, true);
        app(ProductSkus::class)->sync($other, [['options' => ['size' => 'S'], 'qty' => 3]]);
        $this->post(route('admin.orders.manual.store'), $this->form(['lines' => [['product_id' => $this->tee->id, 'sku_id' => $other->fresh()->skuFor(['size' => 'S'])->id, 'qty' => 1]]]))
            ->assertSessionHasErrors('lines.0.sku_id');
        // More than in stock.
        $this->from(route('admin.orders.create'))->post(route('admin.orders.manual.store'), $this->form(['lines' => [['product_id' => $this->tee->id, 'sku_id' => $this->sku('M')->id, 'qty' => 3]]]))
            ->assertRedirect(route('admin.orders.create'))->assertSessionHas('error');
        // Too far back, or in the future.
        $this->post(route('admin.orders.manual.store'), $this->form(['order_date' => now()->subDays(31)->toDateString()]))->assertSessionHasErrors('order_date');
        $this->post(route('admin.orders.manual.store'), $this->form(['order_date' => now()->addDay()->toDateString()]))->assertSessionHasErrors('order_date');
        // "Paid" needs a method.
        $this->post(route('admin.orders.manual.store'), $this->form(['payment' => 'paid']))->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::count());
        $this->assertSame(2, $this->sku('M')->qty);
    }

    public function test_backdated_order_keeps_its_date()
    {
        $this->post(route('admin.orders.manual.store'), $this->form(['order_date' => now()->subDays(3)->toDateString()]));

        $this->assertSame(now()->subDays(3)->toDateString(), Order::first()->created_at->toDateString());
    }

    // ---------------------------------------------------------------- after the order

    public function test_cancelling_a_paid_manual_order_needs_a_manual_refund_and_restores_stock()
    {
        $this->post(route('admin.orders.manual.store'), $this->form(['payment' => 'paid', 'payment_method' => 'cash']));
        $order = Order::first();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'cancelled']);

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(OrderPayment::STATUS_REFUND_PENDING, $order->payment->fresh()->status);
        $this->assertSame(2, $this->sku('M')->qty);
        $this->get(route('admin.orders.show', [$order->encryptedId()]))->assertSee('Refund to send');

        $this->put(route('admin.orders.cod.refund-sent', [$order->encryptedId()]))->assertSessionHas('success');
        $this->assertSame(OrderPayment::STATUS_REFUNDED, $order->payment->fresh()->status);
    }

    public function test_cancelling_an_unpaid_manual_order_needs_no_refund()
    {
        $this->post(route('admin.orders.manual.store'), $this->form(['payment' => 'awaiting']));
        $order = Order::first();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'cancelled']);

        $this->assertSame(OrderPayment::STATUS_CANCELLED, $order->payment->fresh()->status);
    }

    public function test_csv_export_has_channel_discount_and_creator()
    {
        $this->post(route('admin.orders.manual.store'), $this->form(['discount_value' => 50]));

        $csv = $this->get(route('admin.exports.orders'))->assertOk()->getContent();
        $this->assertStringContainsString('Channel,Discount,"Created by"', str_replace(',Created by', ',"Created by"', $csv));
        $this->assertStringContainsString('Facebook,50.00', $csv);
    }

    public function test_staff_without_orders_permission_cannot_create_orders()
    {
        $this->signInAdmin($this->makeAdmin(['role' => 2, 'permissions' => ['products']]));

        $this->get(route('admin.orders.create'))->assertForbidden();
        $this->post(route('admin.orders.manual.store'), $this->form())->assertForbidden();
        $this->assertSame(0, Order::count());
    }
}
