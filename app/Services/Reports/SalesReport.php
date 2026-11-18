<?php

namespace App\Services\Reports;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderProduct;
use App\Models\OrderReturn;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Figures for Admin > Reports.
 *
 * Definitions (shown on the page too):
 * - A sale is an order placed in the range that wasn't cancelled; it belongs to its order date.
 * - Order total = items + shipping + COD fee + tax − coupon − order discount.
 * - Returns = orders from the range that were later returned (including COD parcels not collected).
 * - Net sales = order totals − returns.
 */
class SalesReport
{
    /** @var array<string, Collection> one lightweight row per order, per range */
    private array $rows = [];

    // ------------------------------------------------------------------ rows

    /** Limit an orders query (or a query joined to orders) to sales in the range. */
    public static function scopeSales($query, ReportRange $range)
    {
        return $query->whereBetween('orders.created_at', $range->utcBounds())
            ->where(fn ($q) => $q->whereNull('orders.status')->orWhere('orders.status', '!=', Order::STATUS_CANCELLED))
            ->whereNull('orders.deleted_at');
    }

    /**
     * One row per sale: id, created_at, status, user_id, channel, coupon_id, money columns,
     * items and units (from the order lines), and the payment's provider, method and status.
     */
    public function orderRows(ReportRange $range): Collection
    {
        $key = $range->slug();
        if (isset($this->rows[$key])) {
            return $this->rows[$key];
        }

        $rows = self::scopeSales(Order::query()->withoutGlobalScopes(), $range)
            ->select(['orders.id', 'orders.created_at', 'orders.status', 'orders.user_id', 'orders.channel', 'orders.coupon_id',
                'orders.shipping_charge', 'orders.payment_fee', 'orders.coupon_amount', 'orders.discount_amount', 'orders.tax_total'])
            ->toBase()
            ->get();

        // Line totals and the latest payment per order, looked up in batches (no per-order subqueries).
        $lines = collect();
        $payments = collect();
        foreach ($rows->pluck('id')->chunk(1000) as $ids) {
            $ids = $ids->values()->all();
            $lines = $lines->union(OrderProduct::query()->whereIn('order_id', $ids)->groupBy('order_id')
                ->selectRaw('order_id, SUM(price * qty) as items, SUM(qty) as units')->toBase()->get()->keyBy('order_id'));
            $payments = $payments->union(OrderPayment::query()->whereIn('order_id', $ids)->orderBy('id')
                ->toBase()->get(['order_id', 'provider', 'method', 'status'])->keyBy('order_id'));
        }

        $rows = $rows
            ->each(function ($r) use ($lines, $payments) {
                $line = $lines->get($r->id);
                $pay = $payments->get($r->id);
                $r->items = $line ? $line->items : 0;
                $r->units = $line ? $line->units : 0;
                $r->provider = optional($pay)->provider;
                $r->method = optional($pay)->method;
                $r->payment_status = optional($pay)->status;
            })
            ->map(function ($r) {
                $r->items = (float) $r->items;
                $r->units = (int) $r->units;
                $r->shipping = (float) $r->shipping_charge;
                $r->fee = (float) $r->payment_fee;
                $r->coupon = (float) $r->coupon_amount;
                $r->discount = (float) $r->discount_amount;
                $r->tax = (float) $r->tax_total;
                $r->total = round($r->items + $r->shipping + $r->fee + $r->tax - $r->coupon - $r->discount, 2);
                $r->returned = $r->status === Order::STATUS_RETURNED;
                $r->net = $r->returned ? 0.0 : $r->total;
                $r->channel = $r->channel ?: 'website';

                return $r;
            });

        return $this->rows[$key] = $rows;
    }

    // ------------------------------------------------------------------ overview

    public function summary(ReportRange $range): array
    {
        $rows = $this->orderRows($range);
        $returned = $rows->where('returned', true);

        $s = [
            'orders' => $rows->count(),
            'units' => (int) $rows->sum('units'),
            'items' => round($rows->sum('items'), 2),
            'shipping' => round($rows->sum('shipping'), 2),
            'fees' => round($rows->sum('fee'), 2),
            'tax' => round($rows->sum('tax'), 2),
            'coupons' => round($rows->sum('coupon'), 2),
            'discounts' => round($rows->sum('discount'), 2),
            'gross' => round($rows->sum('total'), 2),
            'returns' => round($returned->sum('total'), 2),
            'returns_count' => $returned->count(),
        ];
        $s['net'] = round($s['gross'] - $s['returns'], 2);
        $s['aov'] = $s['orders'] ? round($s['net'] / $s['orders'], 2) : 0.0;

        return $s;
    }

    /** % change from the previous figure; null when there was nothing before. */
    public static function change(float $current, float $previous): ?float
    {
        if (abs($previous) < 0.005) {
            return abs($current) < 0.005 ? 0.0 : null;
        }

        return round(($current - $previous) / abs($previous) * 100, 1);
    }

    /** Cash still due on shipped COD orders, as of now (not limited to the range). */
    public function codAwaiting(): array
    {
        $orders = Order::where('status', Order::STATUS_SHIPPED)
            ->whereHas('payment', fn ($q) => $q->where('provider', 'cod')->where('status', OrderPayment::STATUS_PENDING))
            ->get(['id', 'gross_total']);

        return ['count' => $orders->count(), 'amount' => round((float) $orders->sum('gross_total'), 2)];
    }

    /** Net sales and orders per day, week or month: key => [label, net, orders]. */
    public function series(ReportRange $range): array
    {
        $series = [];
        foreach ($range->buckets() as $key => $label) {
            $series[$key] = ['label' => $label, 'net' => 0.0, 'orders' => 0];
        }
        foreach ($this->orderRows($range) as $r) {
            $key = $range->bucketKey($r->created_at);
            if (isset($series[$key])) {
                $series[$key]['net'] += $r->net;
                $series[$key]['orders']++;
            }
        }

        return $series;
    }

    // ------------------------------------------------------------------ products and variants

    /** Units, item sales and returns per product, best sellers first. */
    public function products(ReportRange $range): Collection
    {
        $lines = $this->lineTotals($range, 'order_products.product_id');
        $products = Product::withTrashed()->with(['stock', 'category' => fn ($q) => $q->withTrashed()])
            ->whereIn('id', $lines->pluck('product_id'))->get()->keyBy('id');

        return $lines->map(function ($l) use ($products) {
            $p = $products->get($l->product_id);

            return (object) [
                'product_id' => (int) $l->product_id,
                'name' => $p ? $p->name : 'Deleted product #' . $l->product_id,
                'sku' => optional($p)->sku,
                'category_id' => optional($p)->category_id,
                'category' => optional(optional($p)->category)->name,
                'units' => (int) $l->units,
                'sales' => round((float) $l->sales, 2),
                'returned_units' => (int) $l->returned_units,
                'returned' => round((float) $l->returned_value, 2),
                'net' => round((float) $l->sales - (float) $l->returned_value, 2),
                'stock' => $p ? $p->stockQty() : null,
                'deleted' => !$p || $p->trashed(),
            ];
        })->sortByDesc('sales')->values();
    }

    /** The same per variant (size, colour…) of one product. */
    public function variants(ReportRange $range, int $productId): Collection
    {
        $lines = $this->lineTotals($range, 'order_products.sku_id', fn ($q) => $q->where('order_products.product_id', $productId));
        $skus = ProductSku::whereIn('id', $lines->pluck('sku_id')->filter())->get()->keyBy('id');

        return $lines->map(function ($l) use ($skus) {
            $sku = $l->sku_id ? $skus->get($l->sku_id) : null;

            return (object) [
                'sku_id' => $l->sku_id ? (int) $l->sku_id : null,
                'label' => $sku ? $sku->label() : ($l->sku_id ? 'Removed variant' : 'No variant'),
                'sku' => optional($sku)->sku,
                'units' => (int) $l->units,
                'sales' => round((float) $l->sales, 2),
                'returned_units' => (int) $l->returned_units,
                'returned' => round((float) $l->returned_value, 2),
                'net' => round((float) $l->sales - (float) $l->returned_value, 2),
                'stock' => $sku ? max(0, (int) $sku->qty) : null,
            ];
        })->sortByDesc('units')->values();
    }

    private function lineTotals(ReportRange $range, string $groupBy, ?callable $where = null): Collection
    {
        $returned = Order::STATUS_RETURNED;
        $query = OrderProduct::query()
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->selectRaw($groupBy . ' as ' . substr($groupBy, strrpos($groupBy, '.') + 1))
            ->selectRaw('SUM(order_products.qty) as units')
            ->selectRaw('SUM(order_products.price * order_products.qty) as sales')
            ->selectRaw('SUM(CASE WHEN orders.status = ? THEN order_products.qty ELSE 0 END) as returned_units', [$returned])
            ->selectRaw('SUM(CASE WHEN orders.status = ? THEN order_products.price * order_products.qty ELSE 0 END) as returned_value', [$returned])
            ->groupBy($groupBy);
        self::scopeSales($query, $range);
        if ($where) {
            $where($query);
        }

        return $query->toBase()->get();
    }

    // ------------------------------------------------------------------ other breakdowns

    public function categories(ReportRange $range): Collection
    {
        $products = $this->products($range);
        $names = Category::withTrashed()->whereIn('id', $products->pluck('category_id')->filter()->unique())->pluck('name', 'id');
        $totalNet = $products->sum('net');

        return $products->groupBy(fn ($p) => $p->category_id ?: 0)->map(function ($group, $id) use ($names, $totalNet) {
            $net = round($group->sum('net'), 2);

            return (object) [
                'name' => $id ? ($names[$id] ?? 'Deleted category') : 'No category',
                'products' => $group->count(),
                'units' => (int) $group->sum('units'),
                'sales' => round($group->sum('sales'), 2),
                'returned' => round($group->sum('returned'), 2),
                'net' => $net,
                'share' => $totalNet > 0 ? round($net / $totalNet * 100, 1) : 0.0,
            ];
        })->sortByDesc('net')->values();
    }

    public static function paymentLabel(?string $provider, ?string $method): string
    {
        if (!$provider) {
            return 'Not recorded';
        }
        if ($provider === 'manual') {
            return OrderPayment::MANUAL_METHODS[$method] ?? 'Manual payment';
        }

        return ['paypal' => 'PayPal', 'cod' => 'Cash on delivery'][$provider] ?? ucfirst($provider);
    }

    public function payments(ReportRange $range): Collection
    {
        $rows = $this->orderRows($range);

        return $this->groupOrders($rows, fn ($r) => self::paymentLabel($r->provider, $r->provider === 'manual' ? $r->method : null));
    }

    /** COD orders from the range by what happened to the cash. */
    public function cod(ReportRange $range): array
    {
        $cod = $this->orderRows($range)->where('provider', 'cod');
        $bucket = fn ($statuses) => [
            'count' => $cod->whereIn('payment_status', $statuses)->count(),
            'amount' => round($cod->whereIn('payment_status', $statuses)->sum('total'), 2),
        ];

        return [
            'collected' => $bucket([OrderPayment::STATUS_PAID, OrderPayment::STATUS_REFUND_PENDING, OrderPayment::STATUS_REFUNDED]),
            'due' => $bucket([OrderPayment::STATUS_PENDING]),
            'not_collected' => $bucket([OrderPayment::STATUS_FAILED]),
        ];
    }

    public function channels(ReportRange $range): Collection
    {
        return $this->groupOrders($this->orderRows($range), fn ($r) => Order::CHANNELS[$r->channel] ?? ucfirst($r->channel));
    }

    /** Orders, net sales, average order value and share per group. */
    private function groupOrders(Collection $rows, callable $by): Collection
    {
        $totalNet = $rows->sum('net');

        return $rows->groupBy($by)->map(function ($group, $label) use ($totalNet) {
            $net = round($group->sum('net'), 2);

            return (object) [
                'name' => $label,
                'orders' => $group->count(),
                'returns' => $group->where('returned', true)->count(),
                'net' => $net,
                'aov' => $group->count() ? round($net / $group->count(), 2) : 0.0,
                'share' => $totalNet > 0 ? round($net / $totalNet * 100, 1) : 0.0,
            ];
        })->sortByDesc('net')->values();
    }

    public function customers(ReportRange $range, ?int $limit = 10): array
    {
        $rows = $this->orderRows($range);
        $known = $rows->whereNotNull('user_id');
        $ids = $known->pluck('user_id')->unique()->values();

        // A customer is new when their first sale ever falls in this range.
        $firsts = $ids->isEmpty() ? collect() : Order::query()
            ->whereIn('user_id', $ids)
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', Order::STATUS_CANCELLED))
            ->groupBy('user_id')
            ->selectRaw('user_id, MIN(created_at) as first_at')
            ->pluck('first_at', 'user_id');
        $start = $range->utcBounds()[0];
        $new = $ids->filter(fn ($id) => isset($firsts[$id]) && (string) $firsts[$id] >= $start)->count();

        $perCustomer = $known->groupBy('user_id');
        $repeat = $perCustomer->filter(fn ($g) => $g->count() > 1)->count();

        $top = $perCustomer->map(fn ($g, $id) => (object) [
            'user_id' => (int) $id,
            'orders' => $g->count(),
            'net' => round($g->sum('net'), 2),
        ])->sortByDesc('net')->values();
        if ($limit) {
            $top = $top->take($limit);
        }
        $users = User::withTrashed()->whereIn('id', $top->pluck('user_id'))->get(['id', 'name', 'email'])->keyBy('id');
        $top->each(function ($c) use ($users) {
            $u = $users->get($c->user_id);
            $c->name = $u ? $u->name : 'Deleted customer';
            $c->email = optional($u)->email;
        });

        $guests = $rows->whereNull('user_id');

        return [
            'customers' => $ids->count(),
            'new' => $new,
            'returning' => $ids->count() - $new,
            'repeat' => $repeat,
            'repeat_rate' => $ids->count() ? round($repeat / $ids->count() * 100, 1) : 0.0,
            'guest_orders' => $guests->count(),
            'guest_net' => round($guests->sum('net'), 2),
            'top' => $top,
        ];
    }

    public function discounts(ReportRange $range): Collection
    {
        $rows = $this->orderRows($range);
        $coupons = $rows->filter(fn ($r) => $r->coupon_id && $r->coupon > 0)->groupBy('coupon_id');
        $names = Coupon::withTrashed()->whereIn('id', $coupons->keys())->pluck('name', 'id');

        $out = $coupons->map(fn ($g, $id) => (object) [
            'name' => $names[$id] ?? 'Deleted coupon',
            'type' => 'Coupon',
            'uses' => $g->count(),
            'given' => round($g->sum('coupon'), 2),
            'sales' => round($g->sum('net'), 2),
        ])->values();

        $manual = $rows->filter(fn ($r) => $r->discount > 0);
        if ($manual->isNotEmpty()) {
            $out->push((object) [
                'name' => 'Order discount',
                'type' => 'Manual orders',
                'uses' => $manual->count(),
                'given' => round($manual->sum('discount'), 2),
                'sales' => round($manual->sum('net'), 2),
            ]);
        }

        return $out->sortByDesc('given')->values();
    }

    /** Returned orders from the range by reason. */
    public function returns(ReportRange $range): Collection
    {
        $returned = $this->orderRows($range)->where('returned', true);
        $reasons = OrderReturn::whereIn('order_id', $returned->pluck('id'))->get(['order_id', 'reason', 'refund_amount'])->keyBy('order_id');

        return $returned->groupBy(function ($r) use ($reasons) {
            if ($ret = $reasons->get($r->id)) {
                return OrderReturn::REASONS[$ret->reason] ?? ucfirst(str_replace('_', ' ', $ret->reason));
            }

            return $r->provider === 'cod' && $r->payment_status === OrderPayment::STATUS_FAILED ? 'Cash on delivery not collected' : 'Other';
        })->map(fn ($g, $reason) => (object) [
            'name' => $reason,
            'orders' => $g->count(),
            'amount' => round($g->sum('total'), 2),
            'refunded' => round($g->sum(fn ($r) => (float) optional($reasons->get($r->id))->refund_amount), 2),
        ])->sortByDesc('orders')->values();
    }
}
