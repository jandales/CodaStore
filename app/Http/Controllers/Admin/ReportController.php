<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Reports\ReportRange;
use App\Services\Reports\SalesReport;
use Illuminate\Http\Request;

/** Admin > Reports: sales figures for a date range, with breakdowns and CSV downloads. */
class ReportController extends Controller
{
    public const TABS = [
        'overview' => 'Overview',
        'products' => 'Products',
        'categories' => 'Categories',
        'payments' => 'Payment methods',
        'channels' => 'Sales channels',
        'customers' => 'Customers',
        'discounts' => 'Discounts',
        'returns' => 'Returns',
    ];

    public function index(Request $request, SalesReport $report)
    {
        $range = ReportRange::fromRequest($request);
        $tab = $this->tab($request);
        $data = ['range' => $range, 'tab' => $tab, 'tabs' => self::TABS, 'compare' => $request->query('compare', '1') !== '0'];

        switch ($tab) {
            case 'overview':
                $previous = $range->previous();
                $data += [
                    'previous' => $previous,
                    'summary' => $report->summary($range),
                    'before' => $report->summary($previous),
                    'series' => $report->series($range),
                    'cod' => $report->codAwaiting(),
                ];
                break;
            case 'products':
                $product = $request->filled('product') ? Product::withTrashed()->find((int) $request->query('product')) : null;
                $data += $product
                    ? ['product' => $product, 'variants' => $report->variants($range, $product->id)]
                    : ['products' => $report->products($range)];
                break;
            case 'categories':
                $data['categories'] = $report->categories($range);
                break;
            case 'payments':
                $data += ['payments' => $report->payments($range), 'codSplit' => $report->cod($range)];
                break;
            case 'channels':
                $data['channels'] = $report->channels($range);
                break;
            case 'customers':
                $data['customers'] = $report->customers($range);
                break;
            case 'discounts':
                $data['discounts'] = $report->discounts($range);
                break;
            case 'returns':
                $data += ['returns' => $report->returns($range), 'returnedProducts' => $report->products($range)->where('returned_units', '>', 0)->sortByDesc('returned')->values()];
                break;
        }

        return view('admin.reports.index', $data);
    }

    public function export(Request $request, SalesReport $report)
    {
        $range = ReportRange::fromRequest($request);
        $tab = $this->tab($request);
        $rows = [];
        $name = $tab;

        switch ($tab) {
            case 'overview':
                $s = $report->summary($range);
                $p = $report->summary($range->previous());
                $rows[] = ['Figure', $range->label(), 'Previous period (' . $range->previous()->label() . ')', 'Change %'];
                foreach (self::summaryLines() as $key => $label) {
                    $change = SalesReport::change((float) $s[$key], (float) $p[$key]);
                    $rows[] = [$label, $this->num($s[$key], $key), $this->num($p[$key], $key), $change === null ? '' : $change];
                }
                $rows[] = [];
                $rows[] = ['Period', 'Net sales', 'Orders'];
                foreach ($report->series($range) as $point) {
                    $rows[] = [$point['label'], $this->money($point['net']), $point['orders']];
                }
                $name = 'summary';
                break;

            case 'products':
                if ($request->filled('product') && $product = Product::withTrashed()->find((int) $request->query('product'))) {
                    $rows[] = ['Product', 'Variant', 'SKU', 'Units sold', 'Item sales', 'Returned units', 'Returned value', 'Net item sales', 'Stock now'];
                    foreach ($report->variants($range, $product->id) as $v) {
                        $rows[] = [$product->name, $v->label, $v->sku, $v->units, $this->money($v->sales), $v->returned_units, $this->money($v->returned), $this->money($v->net), $v->stock];
                    }
                    $name = 'variants-' . $product->id;
                    break;
                }
                $rows[] = ['Product', 'SKU', 'Category', 'Units sold', 'Item sales', 'Returned units', 'Returned value', 'Net item sales', 'Stock now'];
                foreach ($report->products($range) as $p) {
                    $rows[] = [$p->name, $p->sku, $p->category, $p->units, $this->money($p->sales), $p->returned_units, $this->money($p->returned), $this->money($p->net), $p->stock];
                }
                break;

            case 'categories':
                $rows[] = ['Category', 'Products sold', 'Units sold', 'Item sales', 'Returned value', 'Net item sales', 'Share %'];
                foreach ($report->categories($range) as $c) {
                    $rows[] = [$c->name, $c->products, $c->units, $this->money($c->sales), $this->money($c->returned), $this->money($c->net), $c->share];
                }
                break;

            case 'payments':
            case 'channels':
                $rows[] = [$tab === 'payments' ? 'Payment method' : 'Channel', 'Orders', 'Returned orders', 'Net sales', 'Average order value', 'Share %'];
                foreach ($tab === 'payments' ? $report->payments($range) : $report->channels($range) as $g) {
                    $rows[] = [$g->name, $g->orders, $g->returns, $this->money($g->net), $this->money($g->aov), $g->share];
                }
                if ($tab === 'payments') {
                    $cod = $report->cod($range);
                    $rows[] = [];
                    $rows[] = ['Cash on delivery', 'Orders', 'Amount'];
                    foreach (['collected' => 'Collected', 'due' => 'Cash still due', 'not_collected' => 'Not collected'] as $k => $label) {
                        $rows[] = [$label, $cod[$k]['count'], $this->money($cod[$k]['amount'])];
                    }
                }
                break;

            case 'customers':
                $c = $report->customers($range, null);
                $rows[] = ['Customers', 'New', 'Returning', 'Bought more than once', 'Repeat purchase rate %', 'Guest orders', 'Guest net sales'];
                $rows[] = [$c['customers'], $c['new'], $c['returning'], $c['repeat'], $c['repeat_rate'], $c['guest_orders'], $this->money($c['guest_net'])];
                $rows[] = [];
                $rows[] = ['Customer', 'Email', 'Orders', 'Net sales'];
                foreach ($c['top'] as $t) {
                    $rows[] = [$t->name, $t->email, $t->orders, $this->money($t->net)];
                }
                break;

            case 'discounts':
                $rows[] = ['Discount', 'Type', 'Uses', 'Discount given', 'Net sales on those orders'];
                foreach ($report->discounts($range) as $d) {
                    $rows[] = [$d->name, $d->type, $d->uses, $this->money($d->given), $this->money($d->sales)];
                }
                break;

            case 'returns':
                $rows[] = ['Reason', 'Orders', 'Order value', 'Refunded'];
                foreach ($report->returns($range) as $r) {
                    $rows[] = [$r->name, $r->orders, $this->money($r->amount), $this->money($r->refunded)];
                }
                break;
        }

        return AdminExportController::csv('coda-sales-' . $name . '-' . $range->slug() . '.csv', $rows);
    }

    /** Summary figures: key => label (shared by the page and the CSV). */
    public static function summaryLines(): array
    {
        return [
            'net' => 'Net sales',
            'orders' => 'Orders',
            'aov' => 'Average order value',
            'units' => 'Items sold',
            'items' => 'Item sales',
            'shipping' => 'Shipping fees',
            'fees' => 'COD fees',
            'tax' => 'Tax',
            'coupons' => 'Coupons',
            'discounts' => 'Order discounts',
            'gross' => 'Order totals',
            'returns' => 'Returns and refunds',
            'returns_count' => 'Returned orders',
        ];
    }

    private function tab(Request $request): string
    {
        $tab = (string) $request->query('tab', 'overview');

        return array_key_exists($tab, self::TABS) ? $tab : 'overview';
    }

    private function money($value): string
    {
        return number_format((float) $value, 2, '.', '');
    }

    private function num($value, string $key): string
    {
        return in_array($key, ['orders', 'units', 'returns_count'], true) ? (string) (int) $value : $this->money($value);
    }
}
