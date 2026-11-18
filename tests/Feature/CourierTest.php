<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\ActivityLog;
use App\Models\Courier;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class CourierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
    }

    public function test_existing_couriers_are_carried_over_with_their_codes()
    {
        $this->assertSame(
            ['lbc', 'jnt', 'ninjavan', 'flash', '2go', 'grab', 'lalamove', 'other'],
            Courier::ordered()->pluck('code')->all()
        );
        $this->assertSame('J&T Express', Courier::where('code', 'jnt')->value('name'));
    }

    public function test_admin_adds_a_courier_with_a_tracking_link()
    {
        $this->signInAdmin();

        $this->post(route('admin.couriers.store'), [
            'name' => 'XDE Logistics', 'tracking_url' => 'https://xde.test/track?no={number}', 'sort_order' => 5, 'active' => 1,
        ])->assertRedirect(route('admin.couriers'));

        $courier = Courier::where('name', 'XDE Logistics')->first();
        $this->assertSame('xde-logistics', $courier->code);
        $this->assertTrue($courier->active);
        $this->assertSame('https://xde.test/track?no=AB%2012', $courier->trackingUrlFor('AB 12'));
        $this->assertSame('xde-logistics', Courier::ordered()->value('code'), 'position 5 comes first');
        $this->assertSame('Added courier XDE Logistics', ActivityLog::where('action', 'courier.created')->value('description'));
    }

    public function test_tracking_link_must_be_https_and_contain_the_number_placeholder()
    {
        $this->signInAdmin();

        $this->post(route('admin.couriers.store'), ['name' => 'A', 'tracking_url' => 'http://a.test/track?no={number}'])
            ->assertSessionHasErrors(['tracking_url' => 'The tracking link must start with https://.']);
        $this->post(route('admin.couriers.store'), ['name' => 'A', 'tracking_url' => 'https://a.test/track'])
            ->assertSessionHasErrors(['tracking_url' => 'Put {number} in the tracking link where the tracking number goes.']);
        $this->assertFalse(Courier::where('name', 'A')->exists());
    }

    public function test_codes_stay_unique_and_never_change_on_rename()
    {
        $this->signInAdmin();
        $this->post(route('admin.couriers.store'), ['name' => 'LBC', 'active' => 1]);
        $this->assertTrue(Courier::where('code', 'lbc-2')->exists(), 'lbc is taken by the seeded courier');

        $lbc = Courier::where('code', 'lbc')->first();
        $this->put(route('admin.couriers.update', $lbc), ['name' => 'LBC Express Inc.', 'active' => 1, 'sort_order' => 10]);

        $this->assertSame('lbc', $lbc->fresh()->code);
        $this->assertSame('LBC Express Inc.', $lbc->fresh()->name);
    }

    public function test_only_active_couriers_can_be_chosen_when_shipping()
    {
        $order = $this->createPaidOrder();
        Courier::where('code', 'grab')->update(['active' => false]);
        $this->signInAdmin();

        $this->get(route('admin.orders.show', [$order->encryptedId()]))
            ->assertSee('value="lbc"', false)
            ->assertDontSee('value="grab"', false);

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'shipped', 'courier' => 'grab', 'tracking_number' => 'G1'])
            ->assertSessionHasErrors(['courier' => 'Choose one of the active couriers.']);
        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'shipped', 'courier' => 'made-up', 'tracking_number' => 'G1'])
            ->assertSessionHasErrors('courier');
        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);
    }

    public function test_order_page_shows_status_errors_after_a_failed_update()
    {
        $order = $this->createPaidOrder();
        $this->signInAdmin();
        $show = route('admin.orders.show', [$order->encryptedId()]);

        $this->from($show)->followingRedirects()
            ->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'shipped'])
            ->assertOk()
            ->assertSee('Choose the courier for this shipment.')
            ->assertSee('Enter the tracking number from the courier.');
    }

    public function test_turned_off_courier_still_shows_on_old_orders()
    {
        $order = $this->createPaidOrder(null, ['status' => Order::STATUS_SHIPPED, 'courier' => 'ninjavan', 'tracking_number' => 'NV1']);
        Courier::where('code', 'ninjavan')->update(['active' => false, 'tracking_url' => 'https://nv.test/t/{number}']);

        $order->refresh();
        $this->assertSame('Ninja Van', $order->courierName());
        $this->assertSame('https://nv.test/t/NV1', $order->trackingUrl());

        $this->signInCustomer($order->user);
        $this->get(route('orders.details', [$order->encryptedId()]))->assertSee('Ninja Van')->assertSee('https://nv.test/t/NV1');
    }

    public function test_used_couriers_cannot_be_deleted_but_unused_ones_can()
    {
        $this->createPaidOrder(null, ['status' => Order::STATUS_SHIPPED, 'courier' => 'lbc', 'tracking_number' => 'L1']);
        $this->signInAdmin();
        $lbc = Courier::where('code', 'lbc')->first();
        $flash = Courier::where('code', 'flash')->first();

        $this->delete(route('admin.couriers.destroy', $lbc))->assertSessionHas('error');
        $this->assertTrue(Courier::whereKey($lbc->id)->exists());

        $this->delete(route('admin.couriers.destroy', $flash))->assertRedirect(route('admin.couriers'));
        $this->assertFalse(Courier::whereKey($flash->id)->exists());

        $this->get(route('admin.couriers'))->assertOk()
            ->assertSee('Flash Express deleted.')
            ->assertSee(route('admin.couriers.edit', $lbc), false)
            ->assertDontSee(route('admin.couriers.edit', $flash), false);
    }

    public function test_couriers_page_needs_the_settings_permission()
    {
        $this->seedStoreBasics();
        $this->signInAdmin($this->makeAdmin(['role' => 2, 'permissions' => ['orders']]));

        $this->get(route('admin.couriers'))->assertForbidden();
    }
}
