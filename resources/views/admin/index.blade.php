@extends('layout.admin')

@section('title', 'Dashboard | Coda Admin')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = $admin->firstname ?: $admin->username;

    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $maxIncome = max($monthly_income) ?: 1;
    $currentMonth = (int) now()->format('n');

    $statusParts = [
        ['label' => 'To ship',    'count' => $to_ship,         'color' => '#5B3DF5'],
        ['label' => 'In transit', 'count' => $in_transit,      'color' => '#F59E0B'],
        ['label' => 'Completed',  'count' => $completed_count, 'color' => '#10B981'],
        ['label' => 'Cancelled',  'count' => $cancelled_count, 'color' => '#EF4444'],
        ['label' => 'Returned',   'count' => $returned_count,  'color' => '#94A3B8'],
    ];
    $statusTotal = array_sum(array_column($statusParts, 'count'));
    $stops = []; $acc = 0;
    foreach ($statusParts as $part) {
        if ($part['count'] == 0) continue;
        $from = $acc; $acc += $part['count'] / $statusTotal * 100;
        $stops[] = "{$part['color']} {$from}% {$acc}%";
    }
    $donut = $stops ? 'conic-gradient(' . implode(', ', $stops) . ')' : 'conic-gradient(#ECEAF6 0 100%)';
@endphp

<div class="dash-head">
    <div>
        <h1 class="dash-title">{{ $greeting }}, {{ ucfirst($firstName) }}.</h1>
        <p class="dash-sub">Here's what's happening with your store today.</p>
    </div>
    <div class="dash-actions">
        <a href="{{ route('admin.orders') }}" class="btn btn-light"><i class="fas fa-receipt" aria-hidden="true"></i> View orders</a>
        <a href="/admin/products/add" class="btn btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Add product</a>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-icon stat-icon--violet"><i class="fas fa-receipt" aria-hidden="true"></i></span>
        <p class="stat-label">Total orders</p>
        <p class="stat-value">{{ number_format($order_count) }} <span class="stat-meta stat-meta--up"><i class="fas fa-caret-up" aria-hidden="true"></i> {{ $orders_this_month }} this month</span></p>
    </div>
    <div class="stat-card">
        <span class="stat-icon stat-icon--green"><i class="fas fa-truck-fast" aria-hidden="true"></i></span>
        <p class="stat-label">In transit</p>
        <p class="stat-value">{{ number_format($in_transit) }} <span class="stat-meta">{{ $to_ship }} to ship</span></p>
    </div>
    <div class="stat-card">
        <span class="stat-icon stat-icon--amber"><i class="fas fa-box-open" aria-hidden="true"></i></span>
        <p class="stat-label">Total products</p>
        <p class="stat-value">{{ number_format($product_count) }} <span class="stat-meta">in catalogue</span></p>
    </div>
    <div class="stat-card">
        <span class="stat-icon stat-icon--sky"><i class="fas fa-user-group" aria-hidden="true"></i></span>
        <p class="stat-label">Customers</p>
        <p class="stat-value">{{ number_format($customer_count) }} <span class="stat-meta">{{ $completed_count }} completed orders</span></p>
    </div>
</div>

<section class="dash-card income" aria-labelledby="income-title">
    <div class="income__summary">
        <h2 id="income-title" class="dash-card__title">Income summary</h2>
        <p class="income__label">Total income · {{ now()->year }}</p>
        <p class="income__value">@money($year_income)</p>
        @if (!is_null($income_change))
            <span class="trend {{ $income_change >= 0 ? 'trend--up' : 'trend--down' }}">
                <i class="fas {{ $income_change >= 0 ? 'fa-caret-up' : 'fa-caret-down' }}" aria-hidden="true"></i>
                {{ abs($income_change) }}% vs last month
            </span>
        @else
            <span class="trend trend--flat">No sales last month to compare</span>
        @endif
    </div>

    <div class="income__chart" role="img" aria-label="Monthly income for {{ now()->year }}">
        <div class="chart-bars">
            @foreach ($months as $i => $label)
                @php $value = $monthly_income[$i + 1]; $pct = $value > 0 ? max(4, round($value / $maxIncome * 100)) : 0; @endphp
                <div class="chart-col {{ $i + 1 === $currentMonth ? 'is-current' : '' }}">
                    <div class="chart-bar" style="--h: {{ $pct }}%">
                        <span class="chart-tip">{{ $label }} · @money($value)</span>
                    </div>
                    <span class="chart-month">{{ $label }}</span>
                </div>
            @endforeach
        </div>
        @if ($year_income == 0)
            <p class="chart-empty">No sales yet this year. Income will appear here as orders come in.</p>
        @endif
    </div>
</section>

<div class="dash-row">
    <section class="dash-card" aria-labelledby="orders-title">
        <div class="dash-card__head">
            <div>
                <h2 id="orders-title" class="dash-card__title">Recent orders</h2>
                <p class="dash-card__sub">Latest orders from your store</p>
            </div>
            <a href="{{ route('admin.orders') }}" class="dash-link">View all</a>
        </div>
        @if ($orders->isEmpty())
            <div class="dash-empty">
                <i class="fas fa-receipt" aria-hidden="true"></i>
                <p>No orders yet. New orders will show up here.</p>
            </div>
        @else
            <div class="panel-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>
                                    <a class="link-primary" href="{{ route('admin.orders.show', [$order->encryptedId()]) }}">
                                        <span class="flex items-center gap10">
                                            <span class="avatar-sm"><img src="{{ $order->user->avatar() }}" alt=""></span>
                                            <span>{{ $order->user->name }}</span>
                                        </span>
                                    </a>
                                </td>
                                <td>{{ $order->totalItems() }}</td>
                                <td>@money($order->gross_total)</td>
                                <td>{{ $order->created_at->format('M j, Y') }}</td>
                                <td><span class="status {{ $order->statusColor() }}">{{ $order->status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="dash-card" aria-labelledby="status-title">
        <div class="dash-card__head">
            <div>
                <h2 id="status-title" class="dash-card__title">Order status</h2>
                <p class="dash-card__sub">All orders by current status</p>
            </div>
        </div>
        <div class="donut-wrap">
            <div class="donut" style="background: {{ $donut }}" role="img" aria-label="{{ $statusTotal }} orders by status">
                <div class="donut__hole">
                    <strong>{{ $statusTotal }}</strong>
                    <span>orders</span>
                </div>
            </div>
            <ul class="donut-legend" role="list">
                @foreach ($statusParts as $part)
                    <li>
                        <span class="dot" style="background: {{ $part['color'] }}"></span>
                        <span>{{ $part['label'] }}</span>
                        <strong>{{ $part['count'] }}</strong>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
</div>
@endsection
