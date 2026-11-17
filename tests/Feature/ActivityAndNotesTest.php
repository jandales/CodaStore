<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class ActivityAndNotesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
    }

    public function test_staff_can_add_and_see_notes_on_an_order()
    {
        $order = $this->createPaidOrder();
        $admin = $this->signInAdmin($this->makeAdmin(['firstname' => 'Mia', 'lastname' => 'Reyes']));

        $this->post(route('admin.orders.notes.store', [$order->encryptedId()]), ['body' => "Customer called\nwants gift wrap"])
            ->assertSessionHas('success');

        $note = OrderNote::first();
        $this->assertSame($admin->id, $note->admin_id);
        $this->get(route('admin.orders.show', [$order->encryptedId()]))
            ->assertSee('Notes and activity')
            ->assertSee('Mia Reyes')
            ->assertSee('Customer called<br />', false);

        $this->post(route('admin.orders.notes.store', [$order->encryptedId()]), ['body' => ''])->assertSessionHasErrors('body');
    }

    public function test_notes_are_never_shown_to_the_customer()
    {
        $order = $this->createPaidOrder();
        OrderNote::create(['order_id' => $order->id, 'body' => 'Suspicious address, double check']);

        $this->signInCustomer($order->user);
        $this->get(route('orders.details', [$order->encryptedId()]))->assertOk()->assertDontSee('Suspicious address');
    }

    public function test_only_the_author_or_an_administrator_can_delete_a_note()
    {
        $order = $this->createPaidOrder();
        $author = $this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => ['orders']]);
        $other = $this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => ['orders']]);
        $note = OrderNote::create(['order_id' => $order->id, 'admin_id' => $author->id, 'body' => 'x']);

        $this->signInAdmin($other);
        $this->delete(route('admin.orders.notes.destroy', [$order->encryptedId(), $note->id]))->assertForbidden();

        $this->signInAdmin($author);
        $this->delete(route('admin.orders.notes.destroy', [$order->encryptedId(), $note->id]))->assertSessionHas('success');
        $this->assertSame(0, OrderNote::count());
    }

    public function test_status_changes_are_logged_with_who_did_them()
    {
        $order = $this->createPaidOrder();
        $admin = $this->signInAdmin();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'shipped', 'courier' => 'lbc', 'tracking_number' => 'LBC1']);

        $log = ActivityLog::where('action', 'order.status')->first();
        $this->assertSame($admin->id, $log->admin_id);
        $this->assertSame('Status changed from To ship to Shipped (LBC Express LBC1)', $log->description);
        $this->get(route('admin.orders.show', [$order->encryptedId()]))->assertSee('Status changed from To ship to Shipped');
    }

    public function test_price_and_stock_changes_are_logged()
    {
        $product = $this->createProduct(10, false, ['name' => 'overshirt', 'regular_price' => 1000, 'sale_price' => 0]);
        $this->signInAdmin();

        $product->update(['regular_price' => 900, 'on_sale' => true, 'sale_price' => 700]);
        $product->stock->update(['qty' => 4]);

        $this->assertSame('overshirt: price ₱1,000.00 → ₱900.00; sale price ₱0.00 → ₱700.00; put on sale', ActivityLog::where('action', 'product.updated')->value('description'));
        $this->assertSame('Stock for overshirt: 10 → 4 (-6)', ActivityLog::where('action', 'stock.changed')->value('description'));
    }

    public function test_inventory_adjustment_through_the_admin_is_logged()
    {
        $product = $this->createProduct(10, false, ['name' => 'tee']);
        $this->signInAdmin();

        $this->put(route('admin.inventory.update.quantity', [$product->stock->id]), ['qty' => 5, 'action' => 1]);

        $this->assertSame('Stock for tee: 10 → 15 (+5)', ActivityLog::where('action', 'stock.changed')->value('description'));
    }

    public function test_customer_checkout_changes_are_not_logged()
    {
        $product = $this->createProduct(10, false);
        $product->stock->update(['qty' => 8]); // no admin signed in

        $this->assertSame(0, ActivityLog::count());
    }

    public function test_activity_page_lists_and_filters_entries_for_administrators_only()
    {
        $order = $this->createPaidOrder();
        $this->signInAdmin();
        $order->update(['status' => Order::STATUS_SHIPPED]);
        $order->items->first()->product->stock->update(['qty' => 1]);

        $this->get(route('admin.activity'))->assertOk()->assertSee('Status changed from To ship to Shipped')->assertSee('Stock for overshirt');
        $this->get(route('admin.activity', ['type' => 'stock']))->assertOk()->assertSee('Stock for overshirt')->assertDontSee('Status changed');

        $this->signInAdmin($this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => ['orders', 'products']]));
        $this->get(route('admin.activity'))->assertForbidden();
    }
}
