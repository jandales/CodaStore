{{-- Price with sale treatment. Usage: @include('components.price', ['product' => $product, 'qty' => 1]) --}}
@php $qty = $qty ?? 1; @endphp
@if ($product->isOnSale())
    <span class="price price--sale">
        <span class="price__now">@money($product->effectivePrice() * $qty)</span>
        <s class="price__was" aria-label="Regular price">@money($product->regular_price * $qty)</s>
        @if (empty($compact))<span class="price__off">−{{ $product->discountPercent() }}%</span>@endif
    </span>
@else
    <span class="price">@money($product->effectivePrice() * $qty)</span>
@endif
