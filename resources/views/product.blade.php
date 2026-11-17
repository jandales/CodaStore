@extends('layout.front.app')

@section('title', ucfirst($product->name) . ' | Coda')

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

                @if ($product->minQty())
                    <p class="pdp-stock"><i class="fas fa-circle" aria-hidden="true"></i> {{ $product->minQty() }}</p>
                @endif

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

        <x-featured-product-component/>
    </div>
@endsection
