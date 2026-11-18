@extends('layout.admin')

@section('title', 'Reports | Coda Admin')

@php
    use App\Services\Reports\SalesReport;
    $q = $range->query();
    $link = fn (array $extra = []) => route('admin.reports', array_merge($q, ['tab' => $tab], $compare ? [] : ['compare' => 0], $extra));
    $exportUrl = route('admin.reports.export', array_merge($q, ['tab' => $tab], request()->filled('product') ? ['product' => (int) request('product')] : []));
    $pct = fn ($v) => rtrim(rtrim(number_format((float) $v, 1), '0'), '.') . '%';
@endphp

@section('content')
<div class="page-title rp-head">
    <div>
        <h1>Reports</h1>
        <p class="dash-sub">{{ $range->label() }} · times in {{ str_replace('_', ' ', $range->tz) }}</p>
    </div>
    <a href="{{ $exportUrl }}" class="btn btn-light"><i class="fas fa-download" aria-hidden="true"></i> Download CSV</a>
</div>

<form method="get" action="{{ route('admin.reports') }}" class="panel rp-filter" id="report-range">
    <input type="hidden" name="tab" value="{{ $tab }}">
    @if (request()->filled('product'))<input type="hidden" name="product" value="{{ (int) request('product') }}">@endif
    <div class="form-block">
        <label for="rp-range">Date range</label>
        <select id="rp-range" name="range">
            @foreach (\App\Services\Reports\ReportRange::PRESETS as $key => $label)
                <option value="{{ $key }}" {{ $range->preset === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-block" data-custom>
        <label for="rp-from">From</label>
        <input type="date" id="rp-from" name="from" value="{{ $range->from->format('Y-m-d') }}" max="{{ now($range->tz)->format('Y-m-d') }}">
    </div>
    <div class="form-block" data-custom>
        <label for="rp-to">To</label>
        <input type="date" id="rp-to" name="to" value="{{ $range->to->format('Y-m-d') }}" max="{{ now($range->tz)->format('Y-m-d') }}">
    </div>
    @if ($tab === 'overview')
        <label class="sale-toggle rp-compare"><input type="hidden" name="compare" value="0"><input type="checkbox" name="compare" value="1" {{ $compare ? 'checked' : '' }}> Compare to the previous period</label>
    @endif
    <button type="submit" class="btn btn-primary">Apply</button>
</form>

<nav class="rp-tabs" aria-label="Report sections">
    @foreach ($tabs as $key => $label)
        <a href="{{ route('admin.reports', array_merge($q, ['tab' => $key])) }}" class="{{ $tab === $key ? 'is-active' : '' }}" @if ($tab === $key) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
</nav>

@if ($tab === 'overview')
    @php
        $cards = [
            ['Net sales', 'net', true, 'Order totals minus returns and refunds'],
            ['Orders', 'orders', false, 'Orders placed that weren\'t cancelled'],
            ['Average order value', 'aov', true, 'Net sales ÷ orders'],
            ['Items sold', 'units', false, 'Units across all order lines'],
            ['Returns and refunds', 'returns', true, null],
        ];
    @endphp
    <div class="rp-cards">
        @foreach ($cards as [$label, $key, $isMoney, $help])
            @php $change = SalesReport::change((float) $summary[$key], (float) $before[$key]); @endphp
            <div class="stat-card rp-card">
                <p class="stat-label">{{ $label }}</p>
                <p class="rp-card__value">@if ($isMoney)@money($summary[$key])@else{{ number_format($summary[$key]) }}@endif</p>
                <p class="rp-card__meta">
                    @if ($key === 'returns')
                        {{ $summary['returns_count'] }} {{ \Illuminate\Support\Str::plural('order', $summary['returns_count']) }}
                    @else
                        {{ $help }}
                    @endif
                </p>
                @if ($compare)
                    @php $good = $key === 'returns' ? ($change !== null && $change <= 0) : ($change !== null && $change >= 0); @endphp
                    @if ($change === null)
                        <span class="rp-change rp-change--flat">No sales in the previous period</span>
                    @elseif ($change == 0)
                        <span class="rp-change rp-change--flat">No change</span>
                    @else
                        <span class="rp-change {{ $good ? 'rp-change--good' : 'rp-change--bad' }}">
                            <i class="fas {{ $change > 0 ? 'fa-caret-up' : 'fa-caret-down' }}" aria-hidden="true"></i>
                            {{ $pct(abs($change)) }} <span class="sr-only">{{ $change > 0 ? 'up' : 'down' }}</span> vs {{ $previous->label() }}
                        </span>
                    @endif
                @endif
            </div>
        @endforeach
        <div class="stat-card rp-card">
            <p class="stat-label">COD awaiting collection</p>
            <p class="rp-card__value">@money($cod['amount'])</p>
            <p class="rp-card__meta">{{ $cod['count'] }} shipped {{ \Illuminate\Support\Str::plural('order', $cod['count']) }} · as of today</p>
            @if (auth('admin')->user()->hasPermission('orders') && $cod['count'])
                <a class="dash-link" href="{{ route('admin.payments') }}">See payments</a>
            @endif
        </div>
    </div>

    @php
        $max = max(array_column($series, 'net') ?: [0]) ?: 1;
        $n = count($series);
        $every = (int) max(1, ceil($n / 12));
        $byLabel = ['day' => 'per day', 'week' => 'per week', 'month' => 'per month'][$range->bucket()];
    @endphp
    <section class="dash-card rp-chart" aria-labelledby="rp-chart-title">
        <div class="dash-card__head">
            <div>
                <h2 id="rp-chart-title" class="dash-card__title">Sales over time</h2>
                <p class="dash-card__sub">Net sales {{ $byLabel }}</p>
            </div>
        </div>
        <div class="income__chart" role="img" aria-label="Net sales {{ $byLabel }}, {{ $range->label() }}">
            <div class="chart-bars rp-bars" style="grid-template-columns: repeat({{ $n }}, minmax(0, 1fr));">
                @foreach (array_values($series) as $i => $point)
                    @php $h = $point['net'] > 0 ? max(3, round($point['net'] / $max * 100)) : 0; @endphp
                    <div class="chart-col">
                        <div class="chart-bar" style="--h: {{ $h }}%">
                            <span class="chart-tip">{{ $point['label'] }} · @money($point['net']) · {{ $point['orders'] }} {{ \Illuminate\Support\Str::plural('order', $point['orders']) }}</span>
                        </div>
                        <span class="chart-month">{{ $i % $every === 0 ? $point['label'] : '' }}</span>
                    </div>
                @endforeach
            </div>
            @if ($summary['orders'] === 0)
                <p class="chart-empty">No sales in this period.</p>
            @endif
        </div>
    </section>

    <section class="dash-card rp-section" aria-labelledby="rp-breakdown-title">
        <h2 id="rp-breakdown-title" class="dash-card__title">How net sales add up</h2>
        <table class="table rp-table rp-breakdown">
            <tbody>
                <tr><th scope="row">Item sales <small>{{ number_format($summary['units']) }} items</small></th><td>@money($summary['items'])</td></tr>
                <tr><th scope="row">Shipping fees</th><td>@money($summary['shipping'])</td></tr>
                <tr><th scope="row">COD fees</th><td>@money($summary['fees'])</td></tr>
                @if ($summary['tax'] > 0)<tr><th scope="row">Tax</th><td>@money($summary['tax'])</td></tr>@endif
                <tr><th scope="row">Coupons</th><td>−@money($summary['coupons'])</td></tr>
                <tr><th scope="row">Order discounts</th><td>−@money($summary['discounts'])</td></tr>
                <tr class="rp-subtotal"><th scope="row">Order totals <small>{{ $summary['orders'] }} orders</small></th><td>@money($summary['gross'])</td></tr>
                <tr><th scope="row">Returns and refunds <small>{{ $summary['returns_count'] }} returned</small></th><td>−@money($summary['returns'])</td></tr>
                <tr class="rp-total"><th scope="row">Net sales</th><td>@money($summary['net'])</td></tr>
            </tbody>
        </table>
        <p class="form-help rp-note">A sale is an order that wasn't cancelled, counted on the day it was placed. Returns are orders from this period that were later returned, including cash on delivery parcels the courier couldn't collect.</p>
    </section>

@elseif ($tab === 'products' && isset($product))
    <section class="dash-card rp-section">
        <div class="dash-card__head">
            <div>
                <h2 class="dash-card__title capitalize">{{ $product->name }}</h2>
                <p class="dash-card__sub">Sales per variant · {{ $range->label() }}</p>
            </div>
            <a href="{{ $link(['product' => null]) }}" class="dash-link"><i class="fas fa-arrow-left" aria-hidden="true"></i> All products</a>
        </div>
        @if ($variants->isEmpty())
            <p class="dash-empty">No sales of this product in this period.</p>
        @else
            <div class="table-responsive">
            <table class="table rp-table">
                <thead><tr><th scope="col">Variant</th><th scope="col">SKU</th><th scope="col" class="text-right">Units sold</th><th scope="col" class="text-right">Item sales</th><th scope="col" class="text-right">Returned</th><th scope="col" class="text-right">Net item sales</th><th scope="col" class="text-right">Stock now</th></tr></thead>
                <tbody>
                    @foreach ($variants as $v)
                        <tr>
                            <td><span class="inventory-variant__label">{{ $v->label }}</span></td>
                            <td class="text-muted">{{ $v->sku ?: '—' }}</td>
                            <td class="text-right">{{ number_format($v->units) }}</td>
                            <td class="text-right">@money($v->sales)</td>
                            <td class="text-right">{{ $v->returned_units ? $v->returned_units . ' · ' : '' }}@money($v->returned)</td>
                            <td class="text-right"><strong>@money($v->net)</strong></td>
                            <td class="text-right">@if (is_null($v->stock))—@elseif ($v->stock === 0)<span class="rp-out">Sold out</span>@else{{ $v->stock }}@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </section>

@elseif ($tab === 'products')
    <section class="dash-card rp-section">
        <h2 class="dash-card__title">Products</h2>
        <p class="dash-card__sub">Best sellers first. Choose a product to see its variants (which sizes sell and which run out).</p>
        @if ($products->isEmpty())
            <p class="dash-empty">No sales in this period.</p>
        @else
            <div class="table-responsive">
            <table class="table rp-table">
                <thead><tr><th scope="col">Product</th><th scope="col">Category</th><th scope="col" class="text-right">Units sold</th><th scope="col" class="text-right">Item sales</th><th scope="col" class="text-right">Returned</th><th scope="col" class="text-right">Net item sales</th><th scope="col" class="text-right">Stock now</th></tr></thead>
                <tbody>
                    @foreach ($products as $p)
                        <tr>
                            <td><a href="{{ $link(['product' => $p->product_id]) }}" class="capitalize rp-name">{{ $p->name }}</a>@if ($p->sku)<small class="rp-sub">{{ $p->sku }}</small>@endif</td>
                            <td class="capitalize">{{ $p->category ?: '—' }}</td>
                            <td class="text-right">{{ number_format($p->units) }}</td>
                            <td class="text-right">@money($p->sales)</td>
                            <td class="text-right">{{ $p->returned_units ? $p->returned_units . ' · ' : '' }}@money($p->returned)</td>
                            <td class="text-right"><strong>@money($p->net)</strong></td>
                            <td class="text-right">@if (is_null($p->stock))—@elseif ($p->stock === 0)<span class="rp-out">Sold out</span>@else{{ $p->stock }}@endif</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr><th scope="row" colspan="2">Total</th><td class="text-right">{{ number_format($products->sum('units')) }}</td><td class="text-right">@money($products->sum('sales'))</td><td class="text-right">@money($products->sum('returned'))</td><td class="text-right">@money($products->sum('net'))</td><td></td></tr></tfoot>
            </table>
            </div>
        @endif
    </section>

@elseif ($tab === 'categories')
    <section class="dash-card rp-section">
        <h2 class="dash-card__title">Categories</h2>
        @if ($categories->isEmpty())
            <p class="dash-empty">No sales in this period.</p>
        @else
            <div class="table-responsive">
            <table class="table rp-table">
                <thead><tr><th scope="col">Category</th><th scope="col" class="text-right">Products sold</th><th scope="col" class="text-right">Units sold</th><th scope="col" class="text-right">Item sales</th><th scope="col" class="text-right">Returned</th><th scope="col" class="text-right">Net item sales</th><th scope="col" class="rp-share-col">Share</th></tr></thead>
                <tbody>
                    @foreach ($categories as $c)
                        <tr>
                            <td class="capitalize">{{ $c->name }}</td>
                            <td class="text-right">{{ $c->products }}</td>
                            <td class="text-right">{{ number_format($c->units) }}</td>
                            <td class="text-right">@money($c->sales)</td>
                            <td class="text-right">@money($c->returned)</td>
                            <td class="text-right"><strong>@money($c->net)</strong></td>
                            <td><span class="rp-share"><span style="width: {{ $c->share }}%"></span></span> {{ $pct($c->share) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </section>

@elseif ($tab === 'payments' || $tab === 'channels')
    @php $groups = $tab === 'payments' ? $payments : $channels; @endphp
    <section class="dash-card rp-section">
        <h2 class="dash-card__title">{{ $tab === 'payments' ? 'Payment methods' : 'Sales channels' }}</h2>
        @if ($tab === 'channels')<p class="dash-card__sub">Where orders came from. Manual orders record Facebook, Instagram, phone and so on; checkout orders are Website.</p>@endif
        @if ($groups->isEmpty())
            <p class="dash-empty">No sales in this period.</p>
        @else
            <div class="table-responsive">
            <table class="table rp-table">
                <thead><tr><th scope="col">{{ $tab === 'payments' ? 'Payment method' : 'Channel' }}</th><th scope="col" class="text-right">Orders</th><th scope="col" class="text-right">Returned</th><th scope="col" class="text-right">Net sales</th><th scope="col" class="text-right">Avg. order value</th><th scope="col" class="rp-share-col">Share</th></tr></thead>
                <tbody>
                    @foreach ($groups as $g)
                        <tr>
                            <td>{{ $g->name }}</td>
                            <td class="text-right">{{ number_format($g->orders) }}</td>
                            <td class="text-right">{{ $g->returns }}</td>
                            <td class="text-right"><strong>@money($g->net)</strong></td>
                            <td class="text-right">@money($g->aov)</td>
                            <td><span class="rp-share"><span style="width: {{ $g->share }}%"></span></span> {{ $pct($g->share) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </section>
    @if ($tab === 'payments')
        <section class="dash-card rp-section">
            <h2 class="dash-card__title">Cash on delivery</h2>
            <p class="dash-card__sub">COD orders from this period, by what happened to the cash.</p>
            <div class="rp-mini">
                @foreach (['collected' => 'Collected', 'due' => 'Cash still due', 'not_collected' => 'Not collected'] as $key => $label)
                    <div><span>{{ $label }}</span><strong>@money($codSplit[$key]['amount'])</strong><small>{{ $codSplit[$key]['count'] }} {{ \Illuminate\Support\Str::plural('order', $codSplit[$key]['count']) }}</small></div>
                @endforeach
            </div>
        </section>
    @endif

@elseif ($tab === 'customers')
    <div class="rp-cards rp-cards--4">
        <div class="stat-card rp-card"><p class="stat-label">Customers who ordered</p><p class="rp-card__value">{{ number_format($customers['customers']) }}</p><p class="rp-card__meta">{{ $customers['guest_orders'] }} guest {{ \Illuminate\Support\Str::plural('order', $customers['guest_orders']) }} on top</p></div>
        <div class="stat-card rp-card"><p class="stat-label">New customers</p><p class="rp-card__value">{{ number_format($customers['new']) }}</p><p class="rp-card__meta">First order in this period</p></div>
        <div class="stat-card rp-card"><p class="stat-label">Returning customers</p><p class="rp-card__value">{{ number_format($customers['returning']) }}</p><p class="rp-card__meta">Had ordered before</p></div>
        <div class="stat-card rp-card"><p class="stat-label">Repeat purchase rate</p><p class="rp-card__value">{{ $pct($customers['repeat_rate']) }}</p><p class="rp-card__meta">{{ $customers['repeat'] }} ordered more than once in this period</p></div>
    </div>
    <section class="dash-card rp-section">
        <h2 class="dash-card__title">Top customers</h2>
        @if ($customers['top']->isEmpty())
            <p class="dash-empty">No customer orders in this period.</p>
        @else
            <div class="table-responsive">
            <table class="table rp-table">
                <thead><tr><th scope="col">Customer</th><th scope="col" class="text-right">Orders</th><th scope="col" class="text-right">Net sales</th></tr></thead>
                <tbody>
                    @foreach ($customers['top'] as $c)
                        <tr>
                            <td><span class="capitalize rp-name">{{ $c->name }}</span>@if ($c->email)<small class="rp-sub">{{ $c->email }}</small>@endif</td>
                            <td class="text-right">{{ $c->orders }}</td>
                            <td class="text-right"><strong>@money($c->net)</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <p class="form-help rp-note">The CSV download lists every customer, not only the top 10.</p>
        @endif
    </section>

@elseif ($tab === 'discounts')
    <section class="dash-card rp-section">
        <h2 class="dash-card__title">Discounts</h2>
        @if ($discounts->isEmpty())
            <p class="dash-empty">No coupons or discounts used in this period.</p>
        @else
            <div class="table-responsive">
            <table class="table rp-table">
                <thead><tr><th scope="col">Discount</th><th scope="col">Type</th><th scope="col" class="text-right">Uses</th><th scope="col" class="text-right">Discount given</th><th scope="col" class="text-right">Net sales on those orders</th></tr></thead>
                <tbody>
                    @foreach ($discounts as $d)
                        <tr>
                            <td><strong>{{ $d->name }}</strong></td>
                            <td class="text-muted">{{ $d->type }}</td>
                            <td class="text-right">{{ $d->uses }}</td>
                            <td class="text-right">@money($d->given)</td>
                            <td class="text-right">@money($d->sales)</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </section>

@elseif ($tab === 'returns')
    <section class="dash-card rp-section">
        <h2 class="dash-card__title">Returns by reason</h2>
        <p class="dash-card__sub">Orders placed in this period that were later returned.</p>
        @if ($returns->isEmpty())
            <p class="dash-empty">No returns from this period.</p>
        @else
            <div class="table-responsive">
            <table class="table rp-table">
                <thead><tr><th scope="col">Reason</th><th scope="col" class="text-right">Orders</th><th scope="col" class="text-right">Order value</th><th scope="col" class="text-right">Refunded</th></tr></thead>
                <tbody>
                    @foreach ($returns as $r)
                        <tr><td>{{ $r->name }}</td><td class="text-right">{{ $r->orders }}</td><td class="text-right">@money($r->amount)</td><td class="text-right">@money($r->refunded)</td></tr>
                    @endforeach
                </tbody>
                <tfoot><tr><th scope="row">Total</th><td class="text-right">{{ $returns->sum('orders') }}</td><td class="text-right">@money($returns->sum('amount'))</td><td class="text-right">@money($returns->sum('refunded'))</td></tr></tfoot>
            </table>
            </div>
        @endif
    </section>
    @if ($returnedProducts->isNotEmpty())
        <section class="dash-card rp-section">
            <h2 class="dash-card__title">Returned products</h2>
            <div class="table-responsive">
            <table class="table rp-table">
                <thead><tr><th scope="col">Product</th><th scope="col" class="text-right">Units returned</th><th scope="col" class="text-right">Value</th><th scope="col" class="text-right">Of units sold</th></tr></thead>
                <tbody>
                    @foreach ($returnedProducts as $p)
                        <tr>
                            <td><a href="{{ route('admin.reports', array_merge($q, ['tab' => 'products', 'product' => $p->product_id])) }}" class="capitalize rp-name">{{ $p->name }}</a></td>
                            <td class="text-right">{{ $p->returned_units }}</td>
                            <td class="text-right">@money($p->returned)</td>
                            <td class="text-right">{{ $p->units ? $pct($p->returned_units / $p->units * 100) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </section>
    @endif
@endif
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('report-range');
    if (!form) return;
    var range = form.querySelector('[name="range"]');
    function sync() { form.querySelectorAll('[data-custom]').forEach(function (el) { el.hidden = range.value !== 'custom'; }); }
    range.addEventListener('change', function () { sync(); if (range.value !== 'custom') form.submit(); });
    form.querySelectorAll('[data-custom] input').forEach(function (i) { i.addEventListener('change', function () { range.value = 'custom'; }); });
    sync();
})();
</script>
@endpush
