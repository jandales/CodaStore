<?php

namespace Tests\Feature;

use App\Contracts\PaymentGateway;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Fakes\FakePaymentGateway;
use Tests\TestCase;

class StaffPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->app->instance(PaymentGateway::class, new FakePaymentGateway);
        $this->seedStoreBasics();
    }

    private function staff(array $permissions): Admin
    {
        return $this->signInAdmin($this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => $permissions]));
    }

    public function test_administrators_can_do_everything()
    {
        $admin = $this->makeAdmin(['role' => Admin::ROLE_ADMINISTRATOR]);
        foreach (array_merge(array_keys(Admin::PERMISSIONS), ['staff']) as $permission) {
            $this->assertTrue($admin->hasPermission($permission), $permission);
        }
    }

    public function test_staff_only_reach_the_areas_they_were_given()
    {
        $this->staff(['orders']);

        $this->get(route('admin.orders'))->assertOk();
        $this->get(route('admin.dashboard'))->assertOk();

        $this->get(route('admin.products'))->assertForbidden()->assertSee("You don't have access to Products", false);
        $this->get(route('admin.coupons'))->assertForbidden();
        $this->get(route('admin.customers'))->assertForbidden();
        $this->get(route('admin.setting.general'))->assertForbidden();
        $this->get(route('admin.users'))->assertForbidden();
        $this->get(route('admin.exports.products'))->assertForbidden();
        $this->get(route('admin.exports.orders'))->assertOk();
    }

    public function test_sidebar_only_shows_allowed_areas()
    {
        $this->staff(['products']);

        $this->get(route('admin.dashboard'))
            ->assertSee('Inventory')
            ->assertSee('Import CSV')
            ->assertDontSee('Staff users')
            ->assertDontSee('>Coupons<', false)
            ->assertDontSee('Shipping methods');
    }

    public function test_cancelling_a_paid_order_needs_the_refunds_permission()
    {
        $order = $this->createPaidOrder();
        $this->staff(['orders']);

        $this->get(route('admin.orders.show', [$order->encryptedId()]))->assertDontSee('Cancelled (restores stock and refunds)');
        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'cancelled'])
            ->assertSessionHas('error');
        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);

        $this->staff(['orders', 'refunds']);
        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'cancelled']);
        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_deciding_returns_needs_the_refunds_permission()
    {
        $order = $this->createPaidOrder(null, ['status' => Order::STATUS_COMPLETED, 'delivered_at' => now()]);
        OrderReturn::create(['order_id' => $order->id, 'user_id' => $order->user_id, 'reason' => 'damaged', 'status' => 'requested']);
        $this->staff(['orders']);

        $this->get(route('admin.orders.show', [$order->encryptedId()]))->assertSee('needs to decide this return')->assertDontSee('Approve and refund');
        $this->put(route('admin.orders.return.approve', [$order->encryptedId()]))->assertForbidden();
        $this->assertSame(Order::STATUS_COMPLETED, $order->fresh()->status);
    }

    public function test_administrator_creates_staff_with_permissions()
    {
        $this->signInAdmin();

        $this->post(route('admin.users.store'), [
            'username' => 'packer', 'email' => 'packer@coda.test', 'firstname' => 'Pat', 'lastname' => 'Lim',
            'password' => 'secret-pass-1', 'role' => Admin::ROLE_STAFF, 'permissions' => ['orders', 'products', 'nonsense'],
        ])->assertSessionHasErrors('permissions.2');

        $this->post(route('admin.users.store'), [
            'username' => 'packer', 'email' => 'packer@coda.test', 'firstname' => 'Pat', 'lastname' => 'Lim',
            'password' => 'secret-pass-1', 'role' => Admin::ROLE_STAFF, 'permissions' => ['orders', 'products'],
        ])->assertSessionHasNoErrors();

        $staff = Admin::where('username', 'packer')->first();
        $this->assertSame(['products', 'orders'], $staff->permissions);
        $this->assertFalse($staff->hasPermission('refunds'));
        $this->assertSame('Added packer (Products, Orders)', ActivityLog::where('action', 'staff.created')->value('description'));
    }

    public function test_administrator_changes_staff_access_and_it_is_logged()
    {
        $this->signInAdmin();
        $staff = $this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => ['orders'], 'username' => 'sam']);

        $this->put(route('admin.users.update', [$staff->encryptedId()]), [
            'firstname' => 'Sam', 'lastname' => 'Uy', 'role' => Admin::ROLE_STAFF, 'permissions' => ['orders', 'refunds'],
        ])->assertSessionHas('success');

        $this->assertSame(['orders', 'refunds'], $staff->fresh()->permissions);
        $this->assertSame('Changed access for sam: Orders → Orders, Refunds', ActivityLog::where('action', 'staff.access')->value('description'));
    }

    public function test_staff_cannot_manage_staff_or_change_their_own_access()
    {
        $staff = $this->staff(['orders']);

        $this->post(route('admin.users.store'), ['username' => 'x', 'email' => 'x@coda.test', 'password' => 'secret-pass-1', 'role' => 1])->assertForbidden();
        $this->put(route('admin.update.profile', [$staff->encryptedId()]), ['firstname' => 'Me', 'role' => Admin::ROLE_ADMINISTRATOR, 'permissions' => ['settings']]);

        $staff->refresh();
        $this->assertSame(Admin::ROLE_STAFF, (int) $staff->role);
        $this->assertSame(['orders'], $staff->permissions);
    }

    public function test_existing_staff_keep_full_access_after_the_migration()
    {
        // The migration gives existing non-administrators every staff permission.
        $migration = new \AddPermissionsToAdmins; // loaded by RefreshDatabase
        \Illuminate\Support\Facades\Schema::table('admins', fn ($t) => $t->dropColumn('permissions'));
        $old = $this->makeAdmin(['role' => 0]);
        $migration->up();

        $old = Admin::find($old->id);
        foreach (['products', 'orders', 'refunds', 'customers', 'coupons', 'settings'] as $permission) {
            $this->assertTrue($old->hasPermission($permission), $permission);
        }
        $this->assertFalse($old->hasPermission('staff'));
        $this->assertFalse($old->hasPermission('reports'), 'Reports (revenue) is added later and only given on purpose');
    }
}
