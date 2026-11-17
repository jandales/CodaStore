@php
    $productUrl = route('shop.product', [$product->encryptedId()]);
    $isNew = $product->created_at && $product->created_at->gt(now()->subDays(30));
@endphp
<article class="product-card">
    <a href="{{ $productUrl }}" class="product-card__media" tabindex="-1" aria-hidden="true">
        <img src="{{ $product->imagePath }}" alt="" loading="lazy">
        @if ($product->isSoldOut())
            <span class="product-card__badge product-card__badge--out">Sold out</span>
        @elseif ($product->isOnSale())
            <span class="product-card__badge product-card__badge--sale">Sale</span>
        @elseif ($isNew)
            <span class="product-card__badge">New</span>
        @endif
    </a>
    @include('components.wishlist-heart', ['product' => $product, 'class' => 'product-card__wish'])
    <div class="product-card__body">
        <h3 class="product-card__name"><a href="{{ $productUrl }}">{{ $product->name }}</a></h3>
        <p class="product-card__price">@include('components.price', ['product' => $product])</p>
    </div>
</article>
