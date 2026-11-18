@extends('layout.admin')

@section('title', 'Stock details · ' . $product->name . ' | Coda Admin')

@php
    $hasVariants = $product->skus->isNotEmpty();
    $historyUrl = fn ($variantId = null) => route('admin.inventory.history', array_filter([$product->encryptedId(), 'variant' => $variantId]));
    $canSeeOrders = auth('admin')->user()->hasPermission('orders');
    $tz = \App\Services\Reports\ReportRange::timezone();
@endphp

@section('content')
<nav class="sd-back"><a href="{{ route('admin.inventory') }}" class="dash-link"><i class="fas fa-arrow-left" aria-hidden="true"></i> Inventory</a></nav>

<div class="page-title sd-head">
    <div class="sd-product">
        <div class="image-50"><img src="{{ $variant && $variant->image ? $variant->image : $product->imagePath }}" alt=""></div>
        <div>
            <h1 class="capitalize">{{ $product->name }}@if ($variant) <span class="inventory-variant__label sd-variant">{{ $variant->label() }}</span>@endif</h1>
            <p class="dash-sub">
                Stock details
                @if ($variant && $variant->sku) · SKU {{ $variant->sku }} @elseif ($product->sku) · SKU {{ $product->sku }} @endif
                @if ($product->category) · <span class="capitalize">{{ $product->category->name }}</span>@endif
            </p>
        </div>
    </div>
    <a href="{{ route('admin.products.edit', [$product->encryptedId()]) }}" class="btn btn-light"><i class="fas fa-pen" aria-hidden="true"></i> Edit product</a>
</div>

@if ($hasVariants)
    <nav class="sd-tabs" aria-label="Variants">
        <a href="{{ $historyUrl() }}" class="{{ $variant ? '' : 'is-active' }}" @unless ($variant) aria-current="page" @endunless>All variants <span>{{ $product->stockQty() }}</span></a>
        @foreach ($product->skus as $sku)
            <a href="{{ $historyUrl($sku->id) }}" class="{{ $variant && $variant->id === $sku->id ? 'is-active' : '' }} {{ (int) $sku->qty <= 0 ? 'is-out' : '' }}" @if ($variant && $variant->id === $sku->id) aria-current="page" @endif>
                {{ $sku->label() }} <span>{{ max(0, (int) $sku->qty) }}</span>
            </a>
        @endforeach
    </nav>
@endif

<div class="sd-cards">
    <div class="stat-card sd-card">
        <p class="stat-label">In stock now</p>
        <p class="sd-card__value {{ $inStock <= 0 ? 'is-out' : '' }}">{{ number_format($inStock) }}</p>
        <p class="sd-card__meta">{{ $variant ? $variant->label() : ($hasVariants ? 'All variants' : 'Units') }}</p>
    </div>
    <div class="stat-card sd-card">
        <p class="stat-label">Added · last 30 days</p>
        <p class="sd-card__value sd-in">+{{ number_format($added) }}</p>
        <p class="sd-card__meta">Stock in, restocks and edits</p>
    </div>
    <div class="stat-card sd-card">
        <p class="stat-label">Removed · last 30 days</p>
        <p class="sd-card__value sd-out">−{{ number_format($removed) }}</p>
        <p class="sd-card__meta">Sales, stock out and edits</p>
    </div>
</div>

<section class="dash-card sd-section" aria-labelledby="sd-history">
    <h2 id="sd-history" class="dash-card__title">Stock movements</h2>
    @if ($movements->isEmpty())
        <p class="dash-empty">No stock changes recorded yet.</p>
    @else
        <div class="table-responsive">
            <table class="table sd-table">
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        @if ($hasVariants && !$variant)<th scope="col">Variant</th>@endif
                        <th scope="col">Movement</th>
                        <th scope="col" class="text-right">Change</th>
                        <th scope="col" class="text-right">Stock after</th>
                        <th scope="col">By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movements as $m)
                        <tr>
                            @php $at = $m->created_at->copy()->setTimezone($tz); @endphp
                            <td class="text-nowrap"><time datetime="{{ $at->toIso8601String() }}">{{ $at->format('j M Y') }}</time><small class="sd-sub">{{ $at->format('g:i a') }}</small></td>
                            @if ($hasVariants && !$variant)
                                <td>@if ($m->variant_label)<span class="inventory-variant__label">{{ $m->variant_label }}</span>@else — @endif</td>
                            @endif
                            <td>
                                <span class="sd-reason sd-reason--{{ $m->reason }}"><i class="fas {{ $m->reasonIcon() }}" aria-hidden="true"></i> {{ $m->reasonLabel() }}</span>
                                @if ($m->order)
                                    <small class="sd-sub">
                                        @if ($canSeeOrders && !$m->order->trashed())
                                            <a href="{{ route('admin.orders.show', [$m->order->encryptedId()]) }}">Order {{ $m->order->order_number }}</a>
                                        @else
                                            Order {{ $m->order->order_number }}
                                        @endif
                                        @if ($m->order->channel && $m->order->channel !== 'website') · {{ $m->order->channelLabel() }} @endif
                                    </small>
                                @endif
                                @if ($m->note)<small class="sd-sub">“{{ $m->note }}”</small>@endif
                            </td>
                            <td class="text-right"><strong class="{{ $m->change > 0 ? 'sd-in' : 'sd-out' }}">{{ $m->change > 0 ? '+' : '−' }}{{ number_format(abs($m->change)) }}</strong></td>
                            <td class="text-right">{{ number_format($m->balance) }}</td>
                            <td>
                                @if ($m->admin)
                                    {{ trim($m->admin->fullName()) ?: $m->admin->username }}
                                @elseif ($m->order)
                                    <span class="capitalize">{{ $m->order->customerName() }}</span> <small class="sd-sub">Customer</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $movements->links() }}
    @endif
</section>
@endsection
