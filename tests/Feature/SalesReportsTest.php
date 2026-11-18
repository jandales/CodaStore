<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\User;
use App\Services\Reports\ReportRange;
use App\Services\Reports\SalesReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * A known set of orders, "today" being 18 Nov 2026 at noon in Manila (04:00 UTC):
 *
 *   F  31 Oct 23:30 Manila  user2  completed  PayPal          1 shirt 500                        = 500   (previous period)
 *   G   1 Nov 00:30 Manila  user3  completed  PayPal          1 shirt M 500                      = 500
 *   A   5 Nov               user1  completed  PayPal          2 shirt M 500 + 150 ship           = 1150
 *   B  10 Nov               guest  shipped    COD due         1 shirt L 500 + 100 ship + 30 fee − 50 discount = 580 (Facebook)
 *   C  12 Nov               user1  returned   COD not collected 1 pants 800 + 150 ship           = 950
 *   D  15 Nov               user2  cancelled  PayPal          3 pants 800                         (not a sale)
 *   E  16 Nov               user2  confirmed  GCash (manual)  1 pants 800 − 100 coupon           = 700 (Instagram)
 */
class SalesReportsTest extends TestCase
{
    use RefreshDatabase;

    private Product $shirt;
    private Product $pants;
    private ProductSku $shirtM;
    private ProductSku $shirtL;
    private Coupon $coupon;
    private array $users = [];

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-11-18 04:00:00', 'UTC'));
        $this->seedStoreBasics();
        GeneralSetting::query()->update(['timezone' => 'Asia/Manila']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function seedOrders(): void
    {
        [$hoodies, $jackets] = [Category::where('name', 'hoddie')->first(), Category::where('name', 'jacket')->first()];
        $this->shirt = $this->createProduct(10, false, ['name' => 'boxy shirt', 'sku' => 'SH-1', 'category_id' => $hoodies->id]);
        $this->pants = $this->createProduct(4, false, ['name' => 'cargo pants', 'sku' => 'PA-1', 'category_id' => $jackets->id, 'regular_price' => 800]);
        $this->shirtM = ProductSku::create(['product_id' => $this->shirt->id, 'option_key' => ProductSku::keyFor(['size' => 'M']), 'options' => ['size' => 'M'], 'qty' => 0, 'sku' => 'SH-1-M']);
        $this->shirtL = ProductSku::create(['product_id' => $this->shirt->id, 'option_key' => ProductSku::keyFor(['size' => 'L']), 'options' => ['size' => 'L'], 'qty' => 6, 'sku' => 'SH-1-L']);
        $this->coupon = Coupon::factory()->create(['name' => 'SAVE100']);
        foreach ([1, 2, 3] as $n) {
            $this->users[$n] = User::factory()->create(['name' => "customer {$n}", 'email' => "c{$n}@example.test"]);
        }

        $this->order('2026-10-31 15:30:00', 'completed', 2, [[$this->shirt, null, 1, 500]], ['paypal', 'paid']);                                      // F
        $this->order('2026-10-31 16:30:00', 'completed', 3, [[$this->shirt, $this->shirtM, 1, 500]], ['paypal', 'paid']);                           // G
        $this->order('2026-11-05 02:00:00', 'completed', 1, [[$this->shirt, $this->shirtM, 2, 500]], ['paypal', 'paid'], ['shipping_charge' => 150]); // A
        $this->order('2026-11-10 02:00:00', 'shipped', null, [[$this->shirt, $this->shirtL, 1, 500]], ['cod', 'pending'],
            ['shipping_charge' => 100, 'payment_fee' => 30, 'discount_amount' => 50, 'channel' => 'facebook']);                                    // B
        $this->order('2026-11-12 02:00:00', 'returned', 1, [[$this->pants, null, 1, 800]], ['cod', 'failed'], ['shipping_charge' => 150]);         // C
        $this->order('2026-11-15 02:00:00', 'cancelled', 2, [[$this->pants, null, 3, 800]], ['paypal', 'cancelled']);                               // D
        $this->order('2026-11-16 02:00:00', 'confirmed', 2, [[$this->pants, null, 1, 800]], ['manual', 'paid', 'gcash'],
            ['coupon_id' => $this->coupon->id, 'coupon_amount' => 100, 'channel' => 'instagram']);                                                 // E
    }

    private function order(string $utc, string $status, ?int $user, array $lines, array $payment, array $attributes = []): Order
    {
        $items = array_sum(array_map(fn ($l) => $l[2] * $l[3], $lines));
        $a = array_merge(['shipping_charge' => 0, 'payment_fee' => 0, 'discount_amount' => 0, 'coupon_amount' => 0, 'channel' => 'website'], $attributes);
        $gross = $items + $a['shipping_charge'] + $a['payment_fee'] - $a['discount_amount'] - $a['coupon_amount'];

        $order = Order::factory()->create(array_merge($a, [
            'user_id' => $user ? $this->users[$user]->id : null,
            'status' => $status,
            'gross_total' => $gross,
            'net_total' => $items,
            'num_items_sold' => count($lines),
        ]));
        $order->forceFill(['created_at' => $utc, 'updated_at' => $utc])->save();
        foreach ($lines as [$product, $sku, $qty, $price]) {
            OrderProduct::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'sku_id' => optional($sku)->id, 'qty' => $qty, 'price' => $price]);
        }
        OrderPayment::create(['order_id' => $order->id, 'provider' => $payment[0], 'status' => $payment[1], 'method' => $payment[2] ?? null, 'reference_number' => 'R', 'amount' => (string) $gross]);

        return $order;
    }

    private function november(): ReportRange
    {
        return ReportRange::preset('this_month');
    }

    // ------------------------------------------------------------------ figures

    public function test_summary_figures_match_the_known_orders()
    {
        $this->seedOrders();
        $s = (new SalesReport)->summary($this->november());

        $this->assertSame(5, $s['orders'], 'G, A, B, C, E: cancelled D and October F left out');
        $this->assertSame(6, $s['units']);
        $this->assertEquals(3600, $s['items']);
        $this->assertEquals(400, $s['shipping']);
        $this->assertEquals(30, $s['fees']);
        $this->assertEquals(100, $s['coupons']);
        $this->assertEquals(50, $s['discounts']);
        $this->assertEquals(3880, $s['gross']);
        $this->assertEquals(950, $s['returns']);
        $this->assertSame(1, $s['returns_count']);
        $this->assertEquals(2930, $s['net']);
        $this->assertEquals(586, $s['aov']);
        $this->assertEquals($s['items'] + $s['shipping'] + $s['fees'] + $s['tax'] - $s['coupons'] - $s['discounts'] - $s['returns'], $s['net'], 'the breakdown adds up');
    }

    public function test_days_follow_the_store_timezone()
    {
        $this->seedOrders();
        $series = (new SalesReport)->series($this->november());

        $this->assertSame('day', $this->november()->bucket());
        $this->assertCount(18, $series);
        $this->assertEquals(500, $series['2026-11-01']['net'], 'G at 00:30 Manila counts on 1 Nov, not 31 Oct');
        $this->assertSame(1, $series['2026-11-01']['orders']);
        $this->assertEquals(0, $series['2026-11-12']['net'], 'returned order C adds nothing to net sales');
        $this->assertEquals(2930, array_sum(array_column($series, 'net')));

        $october = new ReportRange(Carbon::parse('2026-10-31', 'Asia/Manila'), Carbon::parse('2026-10-31', 'Asia/Manila'));
        $this->assertSame(1, (new SalesReport)->summary($october)['orders'], 'F at 23:30 Manila is 31 Oct');
    }

    public function test_previous_period_has_the_same_length_and_change_is_right()
    {
        $this->seedOrders();
        $range = $this->november();
        $previous = $range->previous();

        $this->assertSame('2026-10-14', $previous->from->format('Y-m-d'));
        $this->assertSame('2026-10-31', $previous->to->format('Y-m-d'));
        $this->assertSame($range->days(), $previous->days());
        $this->assertEquals(500, (new SalesReport)->summary($previous)['net']);
        $this->assertSame(486.0, SalesReport::change(2930, 500));
        $this->assertSame(-50.0, SalesReport::change(50, 100));
        $this->assertNull(SalesReport::change(100, 0), 'nothing to compare with');
        $this->assertSame(0.0, SalesReport::change(0, 0));
    }

    public function test_cod_awaiting_collection_is_cash_due_on_shipped_cod_orders()
    {
        $this->seedOrders();

        $this->assertSame(['count' => 1, 'amount' => 580.0], (new SalesReport)->codAwaiting());
    }

    // ------------------------------------------------------------------ ranges

    public function test_presets_and_custom_ranges()
    {
        $this->assertSame(['2026-11-18', '2026-11-18'], $this->dates(ReportRange::preset('today')));
        $this->assertSame(['2026-11-17', '2026-11-17'], $this->dates(ReportRange::preset('yesterday')));
        $this->assertSame(['2026-11-12', '2026-11-18'], $this->dates(ReportRange::preset('7d')));
        $this->assertSame(['2026-10-20', '2026-11-18'], $this->dates(ReportRange::preset('30d')));
        $this->assertSame(['2026-10-01', '2026-10-31'], $this->dates(ReportRange::preset('last_month')));
        $this->assertSame(['2026-01-01', '2026-11-18'], $this->dates(ReportRange::preset('this_year')));
        $this->assertSame('month', ReportRange::preset('this_year')->bucket());
        $this->assertSame('week', $this->custom('2026-08-01', '2026-11-18')->bucket());

        $this->assertSame(['2026-11-01', '2026-11-10'], $this->dates($this->custom('2026-11-10', '2026-11-01')), 'swapped');
        $this->assertSame(['2026-11-01', '2026-11-18'], $this->dates($this->custom('2026-11-01', '2027-02-01')), 'not past today');
        $this->assertSame('30d', $this->custom('2026-13-45', 'nope')->preset, 'bad dates fall back to the last 30 days');
        $this->assertSame(['range' => 'custom', 'from' => '2026-11-01', 'to' => '2026-11-10'], $this->custom('2026-11-01', '2026-11-10')->query());
        $this->assertSame(['2026-10-31 16:00:00', '2026-11-18 15:59:59'], $this->november()->utcBounds(), 'Manila days in UTC');
    }

    private function custom(string $from, string $to): ReportRange
    {
        return ReportRange::fromRequest(Request::create('/', 'GET', ['range' => 'custom', 'from' => $from, 'to' => $to]));
    }

    private function dates(ReportRange $r): array
    {
        return [$r->from->format('Y-m-d'), $r->to->format('Y-m-d')];
    }

    // ------------------------------------------------------------------ breakdowns

    public function test_product_variant_and_category_totals_add_up_to_item_sales()
    {
        $this->seedOrders();
        $report = new SalesReport;
        $range = $this->november();

        $products = $report->products($range);
        $this->assertSame(['boxy shirt', 'cargo pants'], $products->pluck('name')->all());
        [$shirt, $pants] = $products->all();
        $this->assertSame([4, 2000.0, 0], [$shirt->units, $shirt->sales, $shirt->returned_units]);
        $this->assertSame([2, 1600.0, 1, 800.0, 800.0], [$pants->units, $pants->sales, $pants->returned_units, $pants->returned, $pants->net]);
        $this->assertEquals($report->summary($range)['items'], $products->sum('sales'));

        $variants = $report->variants($range, $this->shirt->id);
        $this->assertSame(['M', 'L'], $variants->pluck('label')->all());
        $this->assertSame([3, 1], $variants->pluck('units')->all());
        $this->assertSame([0, 6], $variants->pluck('stock')->all(), 'M is sold out');
        $this->assertEquals($shirt->sales, $variants->sum('sales'));

        $categories = $report->categories($range);
        $this->assertSame(['hoddie', 'jacket'], $categories->pluck('name')->all());
        $this->assertEquals(2800, $categories->sum('net'));
        $this->assertEquals(100, $categories->sum('share'));
    }

    public function test_payment_methods_channels_customers_discounts_and_returns()
    {
        $this->seedOrders();
        $report = new SalesReport;
        $range = $this->november();

        $payments = $report->payments($range)->keyBy('name');
        $this->assertSame([2, 1650.0], [$payments['PayPal']->orders, $payments['PayPal']->net]);
        $this->assertSame([2, 1, 580.0], [$payments['Cash on delivery']->orders, $payments['Cash on delivery']->returns, $payments['Cash on delivery']->net]);
        $this->assertSame([1, 700.0], [$payments['GCash']->orders, $payments['GCash']->net]);
        $this->assertEquals(2930, $payments->sum('net'));

        $cod = $report->cod($range);
        $this->assertSame(['count' => 1, 'amount' => 580.0], $cod['due']);
        $this->assertSame(['count' => 1, 'amount' => 950.0], $cod['not_collected']);
        $this->assertSame(0, $cod['collected']['count']);

        $channels = $report->channels($range)->keyBy('name');
        $this->assertEquals(1650, $channels['Website']->net);
        $this->assertEquals(580, $channels['Facebook']->net);
        $this->assertEquals(700, $channels['Instagram']->net);

        $c = $report->customers($range);
        $this->assertSame([3, 2, 1, 1, 33.3], [$c['customers'], $c['new'], $c['returning'], $c['repeat'], $c['repeat_rate']]);
        $this->assertSame([1, 580.0], [$c['guest_orders'], $c['guest_net']]);
        $this->assertSame(['customer 1', 'customer 2', 'customer 3'], $c['top']->pluck('name')->all());
        $this->assertSame([2, 1150.0], [$c['top'][0]->orders, $c['top'][0]->net]);

        $discounts = $report->discounts($range)->keyBy('name');
        $this->assertSame([1, 100.0, 700.0], [$discounts['SAVE100']->uses, $discounts['SAVE100']->given, $discounts['SAVE100']->sales]);
        $this->assertSame([1, 50.0], [$discounts['Order discount']->uses, $discounts['Order discount']->given]);

        $returns = $report->returns($range);
        $this->assertSame('Cash on delivery not collected', $returns[0]->name);
        $this->assertSame([1, 950.0], [$returns[0]->orders, $returns[0]->amount]);
    }

    // ------------------------------------------------------------------ pages, CSV, access

    public function test_every_tab_renders()
    {
        $this->seedOrders();
        $this->signInAdmin();

        $this->get(route('admin.reports', ['range' => 'this_month']))->assertOk()
            ->assertSee('Net sales')->assertSee('₱2,930.00')->assertSee('₱586.00')
            ->assertSee('486%')->assertSee('COD awaiting collection')->assertSee('₱580.00')
            ->assertSee('How net sales add up')->assertSee('1 Nov · ₱500.00 · 1 order');

        foreach (['products' => 'boxy shirt', 'categories' => 'jacket', 'payments' => 'GCash', 'channels' => 'Instagram',
                  'customers' => 'customer 1', 'discounts' => 'SAVE100', 'returns' => 'Cash on delivery not collected'] as $tab => $text) {
            $this->get(route('admin.reports', ['range' => 'this_month', 'tab' => $tab]))->assertOk()->assertSee($text);
        }
        $this->get(route('admin.reports', ['range' => 'this_month', 'tab' => 'products', 'product' => $this->shirt->id]))
            ->assertOk()->assertSee('Sales per variant')->assertSee('Sold out');
        $this->get(route('admin.reports', ['tab' => 'nonsense', 'range' => 'nonsense']))->assertOk()->assertSee('Sales over time');
    }

    public function test_csv_exports_match_the_figures_and_are_named_by_range()
    {
        $this->seedOrders();
        $this->signInAdmin();

        $summary = $this->get(route('admin.reports.export', ['range' => 'this_month']));
        $summary->assertOk()->assertHeader('Content-Disposition', 'attachment; filename="coda-sales-summary-2026-11-01-to-2026-11-18.csv"');
        $csv = $summary->getContent();
        $this->assertStringContainsString('"Net sales",2930.00,500.00,486', $csv);
        $this->assertStringContainsString('"1 Nov",500.00,1', $csv);

        $products = $this->get(route('admin.reports.export', ['range' => 'this_month', 'tab' => 'products']));
        $products->assertHeader('Content-Disposition', 'attachment; filename="coda-sales-products-2026-11-01-to-2026-11-18.csv"');
        $this->assertStringContainsString('"cargo pants",PA-1,jacket,2,1600.00,1,800.00,800.00', $products->getContent());

        $variants = $this->get(route('admin.reports.export', ['range' => 'this_month', 'tab' => 'products', 'product' => $this->shirt->id]))->getContent();
        $this->assertStringContainsString('"boxy shirt",M,SH-1-M,3,1500.00,0,0.00,1500.00,0', $variants);

        $customers = $this->get(route('admin.reports.export', ['range' => 'this_month', 'tab' => 'customers']))->getContent();
        $this->assertStringContainsString('3,2,1,1,33.3,1,580.00', $customers);
    }

    public function test_reports_need_their_own_permission()
    {
        $this->seedOrders();
        $orders = $this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => ['orders']]);
        $this->signInAdmin($orders);
        $this->get(route('admin.reports'))->assertForbidden();
        $this->get(route('admin.reports.export'))->assertForbidden();
        $this->get(route('admin.orders'))->assertDontSee(route('admin.reports'));

        $this->signInAdmin($this->makeAdmin(['role' => Admin::ROLE_STAFF, 'permissions' => ['reports']]));
        $this->get(route('admin.reports'))->assertOk();
    }

    public function test_dashboard_links_to_reports_and_counts_income_in_store_time()
    {
        $this->seedOrders();
        $this->signInAdmin();

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('View reports')->assertSee(route('admin.reports'), false)
            ->assertSee('Nov · ₱2,930.00', false);
    }
}
