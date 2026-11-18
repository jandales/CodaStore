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
                {{ $sku->label() }} <span>{{ max(0, (int) $sku->qty) }}</span>@if ((int) $sku->damaged_qty > 0)<em class="sd-tab-damaged">{{ (int) $sku->damaged_qty }} damaged</em>@endif
            </a>
        @endforeach
    </nav>
@endif

<div class="sd-cards sd-cards--4">
    <div class="stat-card sd-card">
        <p class="stat-label">Available now</p>
        <p class="sd-card__value {{ $inStock <= 0 ? 'is-out' : '' }}">{{ number_format($inStock) }}</p>
        <p class="sd-card__meta">Can be sold · {{ $variant ? $variant->label() : ($hasVariants ? 'all variants' : 'units') }}</p>
    </div>
    <div class="stat-card sd-card">
        <p class="stat-label">Damaged now</p>
        <p class="sd-card__value {{ $damaged > 0 ? 'sd-out' : '' }}">{{ number_format($damaged) }}</p>
        <p class="sd-card__meta">Kept apart until repaired or written off</p>
    </div>
    <div class="stat-card sd-card">
        <p class="stat-label">On hand</p>
        <p class="sd-card__value">{{ number_format($onHand) }}</p>
        <p class="sd-card__meta">Available + damaged</p>
    </div>
    <div class="stat-card sd-card">
        <p class="stat-label">Damaged · last 30 days</p>
        <p class="sd-card__value">{{ number_format($damagedRecently) }}</p>
        <p class="sd-card__meta">Added {{ number_format($added) }} · removed {{ number_format($removed) }}</p>
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
                        <th scope="col" class="text-right">Available</th>
                        <th scope="col" class="text-right">Damaged</th>
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
                            <td class="text-right text-nowrap">
                                @if ($m->change !== 0)<strong class="{{ $m->change > 0 ? 'sd-in' : 'sd-out' }}">{{ $m->change > 0 ? '+' : '−' }}{{ number_format(abs($m->change)) }}</strong>@else<span class="text-muted">0</span>@endif
                                <small class="sd-sub">→ {{ number_format($m->balance) }}</small>
                            </td>
                            <td class="text-right text-nowrap">
                                @if ($m->damaged_change !== 0)
                                    <strong class="sd-damaged">{{ $m->damaged_change > 0 ? '+' : '−' }}{{ number_format(abs($m->damaged_change)) }}</strong>
                                    <small class="sd-sub">→ {{ number_format($m->damaged_balance) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
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
