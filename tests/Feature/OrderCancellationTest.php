<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Stock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    /** An order for $qty units of a product that has $stockLeft units left after the sale. */
    private function orderFor($user, int $qty = 2, int $stockLeft = 8, array $orderAttributes = []): array
    {
        $product = $this->createProduct($stockLeft);
        $order = Order::factory()->create(array_merge(['user_id' => $user->id], $orderAttributes));
        OrderProduct::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'qty' => $qty]);

        return [$order, $product];
    }

    private function stockOf($product): int
    {
        return (int) Stock::where('product_id', $product->id)->value('qty');
    }

    public function test_customer_cancel_sets_cancelled_status_and_restores_stock()
    {
        $user = $this->signInCustomer();
        [$order, $product] = $this->orderFor($user, 2, 8);

        $this->put(route('orders.cancel', [$order->encryptedId()]))
            ->assertSessionHas('success', 'Your order has been cancelled');

        $order->refresh();
        $this->assertSame(Order::STATUS_CANCELLED, $order->status);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame(10, $this->stockOf($product));
    }

    public function test_shipped_order_cannot_be_cancelled()
    {
        $user = $this->signInCustomer();
        [$order, $product] = $this->orderFor($user, 2, 8, ['status' => Order::STATUS_SHIPPED]);

        $this->put(route('orders.cancel', [$order->encryptedId()]))
            ->assertSessionHas('error');

        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);
        $this->assertSame(8, $this->stockOf($product));
    }

    public function test_cancelling_twice_restores_stock_only_once()
    {
        $user = $this->signInCustomer();
        [$order, $product] = $this->orderFor($user, 3, 7);

        $this->put(route('orders.cancel', [$order->encryptedId()]));
        $this->put(route('orders.cancel', [$order->encryptedId()]))->assertSessionHas('error');

        $this->assertSame(10, $this->stockOf($product));
    }

    public function test_customer_cannot_cancel_someone_elses_order()
    {
        $owner = \App\Models\User::factory()->create();
        [$order, $product] = $this->orderFor($owner);
        $this->signInCustomer();

        $this->put(route('orders.cancel', [$order->encryptedId()]))->assertForbidden();
        $this->assertSame(Order::STATUS_CONFIRMED, $order->fresh()->status);
    }

    public function test_cancel_gives_the_coupon_use_back()
    {
        $user = $this->signInCustomer();
        $coupon = Coupon::factory()->create(['usage' => 3]);
        [$order] = $this->orderFor($user, 1, 9, ['coupon_id' => $coupon->id]);

        $this->put(route('orders.cancel', [$order->encryptedId()]));

        $this->assertSame(2, (int) $coupon->fresh()->usage);
    }

    public function test_admin_cancel_also_restores_stock()
    {
        $customer = \App\Models\User::factory()->create();
        [$order, $product] = $this->orderFor($customer, 2, 8);
        $this->signInAdmin();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'cancelled']);

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(10, $this->stockOf($product));
    }

    public function test_marking_completed_does_not_set_cancelled_at()
    {
        $customer = \App\Models\User::factory()->create();
        [$order] = $this->orderFor($customer, 1, 9, ['status' => Order::STATUS_SHIPPED]);
        $this->signInAdmin();

        $this->put(route('admin.orders.shipped1', [$order->encryptedId()]), ['status' => 'completed']);

        $order->refresh();
        $this->assertSame(Order::STATUS_COMPLETED, $order->status);
        $this->assertNotNull($order->delivered_at);
        $this->assertNull($order->cancelled_at);
    }

    public function test_legacy_canceled_rows_are_normalised()
    {
        $order = Order::factory()->create(['status' => 'canceled']);
        (new \NormaliseCancelledOrderStatus)->up();

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
    }
}
