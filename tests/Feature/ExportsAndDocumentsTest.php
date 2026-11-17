<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminExportController;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportsAndDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private function csv($response): array
    {
        $body = ltrim($response->getContent(), "\xEF\xBB\xBF");
        $lines = array_values(array_filter(preg_split('/\r?\n/', $body), 'strlen'));

        return array_map('str_getcsv', $lines);
    }

    public function test_orders_export()
    {
        $order = $this->createPaidOrder(null, ['order_number' => 'CODA-1001', 'courier' => 'lbc', 'tracking_number' => 'LBC9']);
        $this->signInAdmin();

        $response = $this->get(route('admin.exports.orders'))->assertOk();

        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="coda-orders-', $response->headers->get('Content-Disposition'));
        $rows = $this->csv($response);
        $this->assertSame('Order number', $rows[0][0]);
        $this->assertSame(['CODA-1001', 'Ana Cruz', 'ana@example.test', '2', '1000.00', '150.00', '1150.00', 'paid', 'CAP-123', 'LBC9'],
            [$rows[1][0], $rows[1][3], $rows[1][4], $rows[1][5], $rows[1][6], $rows[1][7], $rows[1][9], $rows[1][10], $rows[1][11], $rows[1][13]]);
    }

    public function test_orders_export_can_be_filtered_by_status()
    {
        $this->createPaidOrder(null, ['order_number' => 'A-1', 'status' => Order::STATUS_CONFIRMED]);
        $this->createPaidOrder(null, ['order_number' => 'B-2', 'status' => Order::STATUS_SHIPPED]);
        $this->signInAdmin();

        $rows = $this->csv($this->get(route('admin.exports.orders', ['status' => 'shipped'])));

        $this->assertCount(2, $rows);
        $this->assertSame('B-2', $rows[1][0]);
    }

    public function test_customers_and_products_exports()
    {
        $user = User::factory()->create(['name' => 'Ana Cruz', 'email' => 'ana@example.test']);
        $this->createPaidOrder($user);
        $this->createProduct(4, false, ['name' => 'sale coat', 'regular_price' => 2000, 'sale_price' => 1500, 'on_sale' => true, 'sku' => 'COAT-1']);
        $this->signInAdmin();

        $customers = $this->csv($this->get(route('admin.exports.customers'))->assertOk());
        $this->assertSame(['Ana Cruz', 'ana@example.test'], array_slice($customers[1], 0, 2));
        $this->assertSame(['1', '1150.00'], array_slice($customers[1], 4, 2));

        $products = collect($this->csv($this->get(route('admin.exports.products'))->assertOk()))->keyBy(0);
        $this->assertSame(['sale coat', 'COAT-1', 'hoddie', 'Published', '2000.00', '1500.00', 'Yes', '1500.00', '4'], $products['sale coat']);
    }

    public function test_formula_like_cells_are_neutralised()
    {
        $this->assertSame("'=HYPERLINK(\"x\")", AdminExportController::safeCell('=HYPERLINK("x")'));
        $this->assertSame("'@cmd", AdminExportController::safeCell('@cmd'));
        $this->assertSame('-150.00', AdminExportController::safeCell('-150.00'), 'negative numbers stay numbers');
        $this->assertSame('Ana', AdminExportController::safeCell('Ana'));
    }

    public function test_exports_need_an_admin()
    {
        $this->get(route('admin.exports.orders'))->assertRedirect();
        $this->signInCustomer();
        $this->get(route('admin.exports.orders'))->assertRedirect();
    }

    public function test_admin_invoice_and_packing_slip()
    {
        $order = $this->createPaidOrder(null, ['order_number' => 'CODA-77']);
        $this->signInAdmin();

        $this->get(route('admin.orders.invoice', [$order->encryptedId()]))
            ->assertOk()
            ->assertSee('Invoice')
            ->assertSee('CODA-77')
            ->assertSee('Ana Cruz')
            ->assertSee('₱1,000.00')   // 2 x 500 line total
            ->assertSee('₱1,150.00')
            ->assertSee('Paid with PayPal');

        $this->get(route('admin.orders.packing-slip', [$order->encryptedId()]))
            ->assertOk()
            ->assertSee('Packing slip')
            ->assertSee('OVR-001')
            ->assertSee('Size: M')
            ->assertDontSee('₱1,150.00');
    }

    public function test_customer_can_view_only_their_own_invoice()
    {
        $owner = User::factory()->create();
        $order = $this->createPaidOrder($owner);

        $this->signInCustomer($owner);
        $this->get(route('orders.invoice', [$order->encryptedId()]))->assertOk()->assertSee('Invoice');
        $this->get(route('orders.details', [$order->encryptedId()]))->assertSee('View invoice');

        $this->signInCustomer();
        $this->get(route('orders.invoice', [$order->encryptedId()]))->assertForbidden();
    }

    public function test_admin_order_page_subtotal_counts_quantities()
    {
        $order = $this->createPaidOrder();
        $this->assertSame(1000, (int) $order->subtotal());
    }
}
