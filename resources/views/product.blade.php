@extends('layout.front.app')

@section('title', ucfirst($product->name) . ' | Coda')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->short_description ?: $product->long_description ?: ucfirst($product->name) . ' from Coda.'), 155))
@section('meta_image', url($product->imagePath))
@section('og_type', 'product')
@push('head')
    <meta property="product:price:amount" content="{{ number_format($product->effectivePrice(), 2, '.', '') }}">
    <meta property="product:price:currency" content="PHP">
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => ucfirst($product->name),
        'image' => [url($product->imagePath)],
        'description' => strip_tags($product->short_description ?: $product->long_description ?: ''),
        'sku' => $product->sku,
        'brand' => ['@type' => 'Brand', 'name' => 'Coda'],
        'aggregateRating' => $product->reviews->count() ? [
            '@type' => 'AggregateRating',
            'ratingValue' => round($product->reviews->avg('rating'), 1),
            'reviewCount' => $product->reviews->count(),
        ] : null,
        'offers' => [
            '@type' => 'Offer',
            'url' => $product->url(),
            'priceCurrency' => 'PHP',
            'price' => number_format($product->effectivePrice(), 2, '.', ''),
            'availability' => $product->isSoldOut() ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock',
        ],
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
    <div class="container pdp">
        {{-- Review messages are shown inside the Reviews tab instead. --}}
        @if (session('success') && !session('review_success'))
            <div class="alert alert-success mt-1" role="status">{{ session('success') }}</div>
        @endif

        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('shop') }}">Shop</a></li>
                <li aria-current="page" class="capitalize">{{ $product->name }}</li>
            </ol>
        </nav>

        <div class="panel-view pdp-main">
            <div class="product-images pdp-gallery">
                <div class="thumbnails-wrapper pdp-thumbs">
                    <ul>
                        @foreach ($product->photos as $i => $photo)
                            <li>
                                <div class="thumbnail" role="button" tabindex="0" aria-label="Show image {{ $i + 1 }}">
                                    <img class="thumbnail-{{ $i }}" src="{{ $photo->path }}" alt="">
                                    <div class="thumbnail-overlay"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="full-image pdp-image">
                    <img id="fullImage" src="{{ $product->imagePath }}" alt="{{ $product->name }}">
                    @if ($product->photos->count() > 1)
                        <button type="button" class="arrow arrow-left align-items" aria-label="Previous image"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                        <button type="button" class="arrow arrow-right align-items" aria-label="Next image"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                    @endif
                </div>
            </div>

            <div class="description pdp-info">
                <h1 class="title pdp-title capitalize">{{ $product->name }}</h1>
                <p class="pdp-price">@include('components.price', ['product' => $product])</p>

                <div class="pdp-rating">
                    @if ($product->reviews->count() > 0)
                        <x-rating :rating="$product->rating()" />
                        <a href="#product-tabs" class="link-underline">{{ $product->reviews->count() }} {{ Str::plural('review', $product->reviews->count()) }}</a>
                    @else
                        <span class="pdp-muted">No reviews yet</span>
                    @endif
                </div>

                @if ($product->short_description)
                    <p class="details pdp-summary">{{ $product->short_description }}</p>
                @endif

                <ul class="variants pdp-variants">
                    <x-variants :product="$product" />
                </ul>
                @if ($product->hasSkus())
                    <p id="pdp-variant-stock" class="pdp-stock" role="status" hidden></p>
                    <script type="application/json" id="variant-data">{!! json_encode([
                        'threshold' => \App\Models\GeneralSetting::lowStockThreshold(),
                        'baseImage' => $product->imagePath,
                        'basePriceHtml' => view('components.price', ['product' => $product])->render(),
                        'skus' => $product->skus->map(fn ($sku) => [
                            'options' => $sku->options,
                            'qty' => (int) $sku->qty,
                            'image' => $sku->image,
                            'priceHtml' => view('components.price', ['product' => $product, 'sku' => $sku])->render(),
                        ])->values(),
                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                    <script src="{{ asset('js/product-variants.js') }}?v=1" defer></script>
                @endif

                @if ($product->minQty())
                    <p class="pdp-stock {{ $product->isSoldOut() ? 'pdp-stock--out' : '' }}"><i class="fas fa-circle" aria-hidden="true"></i> {{ $product->minQty() }}</p>
                @endif

                @if ($product->isSoldOut())
                <div class="pdp-buy">
                    <button type="button" class="btn btn-dark pdp-add pdp-add--soldout" disabled aria-disabled="true">Sold out</button>
                    <p class="pdp-muted">This item is out of stock right now.</p>
                </div>
                @else
                <div class="pdp-buy">
                    <label for="quantity-input" class="pdp-label">Quantity</label>
                    <div class="pdp-buy__row">
                        <div class="product cart-form-group pdp-qty">
                            <div class="btn-num-product-down flex-vert-center btn-add-minus" type="minus" role="button" aria-label="Decrease quantity"><i class="fas fa-minus" aria-hidden="true"></i></div>
                            <input id="quantity-input" class="num-product" name="qty" value="1" type="number" min="1" inputmode="numeric">
                            <div class="btn-num-product-up flex-vert-center btn-add-minus" type="add" role="button" aria-label="Increase quantity"><i class="fas fa-plus" aria-hidden="true"></i></div>
                        </div>
                        <button id="cart-button" data-id="{{ $product->encryptedId() }}" url="{{ route('cart.store', [$product->encryptedId()]) }}" class="btn btn-dark btn-add-cart pdp-add">Add to bag</button>
                    </div>
                </div>
                @endif

                @include('components.wishlist-heart', ['product' => $product, 'class' => 'pdp-wish', 'label' => true])
                @if (session('wishlist_message'))
                    <p class="pdp-muted" role="status">{{ session('wishlist_message') }}</p>
                @endif

                <ul class="pdp-perks" role="list">
                    <li><i class="fas fa-truck" aria-hidden="true"></i> Shipping calculated at checkout</li>
                    <li><i class="fas fa-rotate-left" aria-hidden="true"></i> Free returns within 30 days</li>
                    <li><i class="fas fa-lock" aria-hidden="true"></i> Secure checkout</li>
                </ul>
            </div>
        </div>

        <div id="product-tabs" class="product-additional-info-wrapper pdp-tabs">
            <div class="tabs tab-product-detail">
                <ul class="tab-navigation" role="tablist">
                    <li><a class="tabs-button" data-for-tab="1" role="tab" tabindex="0">Description</a></li>
                    <li><a class="tabs-button" data-for-tab="2" role="tab" tabindex="0">Reviews ({{ $product->reviews->count() }})</a></li>
                </ul>
                <div class="tabs-content" data-tab="1" role="tabpanel">
                    <div class="additional-description">
                        <p>{{ $product->long_description }}</p>
                    </div>
                </div>
                <div class="tabs-content" data-tab="2" role="tabpanel">
                    <x-product-review :product="$product" />
                </div>
            </div>
        </div>

        @php $related = $product->related(); @endphp
        @if ($related->isNotEmpty())
            <section class="home-block related-products" aria-labelledby="related-title">
                <div class="home-block__head">
                    <h2 id="related-title" class="home-block__title">You may also like</h2>
                    @if ($product->category)
                        <a href="{{ route('shop.category', [$product->category]) }}" class="link-underline">More {{ $product->category->name }}</a>
                    @endif
                </div>
                <div class="product-grid">
                    @foreach ($related as $item)
                        @include('components.product-card', ['product' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
