{{--
    Price with sale treatment.
    Usage: @include('components.price', ['product' => $product, 'qty' => 1, 'sku' => $variant])
    With a variant (sku) it shows that variant's price; without one, a product whose variants cost
    different amounts shows "From" its lowest price.
--}}
@php
    $qty = $qty ?? 1;
    $sku = $sku ?? null;
    $now = $sku ? $sku->effectivePrice() : $product->effectivePrice();
    $was = $sku ? $sku->basePrice() : $product->basePrice();
    $from = !$sku && $product->hasVariablePrice();
@endphp
@if ($product->isOnSale())
    <span class="price price--sale">
        @if ($from)<span class="price__from">From</span>@endif
        <span class="price__now">@money($now * $qty)</span>
        <s class="price__was" aria-label="Regular price">@money($was * $qty)</s>
        @if (empty($compact))<span class="price__off">−{{ $product->discountPercent() }}%</span>@endif
    </span>
@else
    <span class="price">@if ($from)<span class="price__from">From</span> @endif@money($now * $qty)</span>
@endif
