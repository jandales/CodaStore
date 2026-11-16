@extends('layout.front.app')

@section('title', 'Your bag | Coda')

@section('content')
    <div class="container cart-page">
        <header class="cart-head">
            <h1 class="cart-head__title">Your bag</h1>
            @if (!empty($cart) && $cart->items->count() > 0)
                <p class="cart-head__count">{{ $cart->totalItems() }} {{ Str::plural('item', $cart->totalItems()) }}</p>
            @endif
        </header>

        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if (session('message'))
            <div class="alert alert-warning" role="status">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
        @endif

        @if (empty($cart) || $cart->items->count() == 0)
            <div class="empty-state cart-empty">
                <i class="fas fa-shopping-bag" aria-hidden="true"></i>
                <h2>Your bag is empty</h2>
                <p>Find something you'll wear often.</p>
                <a href="{{ route('shop') }}" class="btn btn-dark">Shop new in</a>
            </div>
        @else
            <div class="cart-layout">
                <div class="cart-lines">
                    <table class="cart-table-1 cart-lines__table">
                        <thead>
                            <tr>
                                <th scope="col">Product</th>
                                <th scope="col">Quantity</th>
                                <th scope="col" class="text-align-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cart->items as $item)
                                @php $itemUrl = route('shop.product', [$item->product->encryptedId()]); @endphp
                                <tr>
                                    <td>
                                        <div class="cart-line">
                                            <a href="{{ $itemUrl }}" class="cart-line__image" tabindex="-1" aria-hidden="true">
                                                <img src="{{ $item->product->imagePath }}" alt="">
                                            </a>
                                            <div class="cart-line__info">
                                                <a class="cart-line__name capitalize" href="{{ $itemUrl }}">{{ $item->product->name }}</a>
                                                <span class="cart-line__meta">@money($item->product->regular_price)</span>
                                                @if ($item->attributes != null)
                                                    @foreach ($item->attributes as $key => $variant)
                                                        <span class="cart-line__meta capitalize">{{ $key }}: {{ $variant }}</span>
                                                    @endforeach
                                                @endif
                                                <button type="button" class="cart-item-remove cart-line__remove" data-id="{{ $item->id }}">Remove</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="cart-form-group cart-line__qty">
                                            <div class="btn-num-product-down flex-vert-center add-less-quantity" type="less" role="button" aria-label="Decrease quantity"><i class="fas fa-minus" aria-hidden="true"></i></div>
                                            <input class="cart-qty num-product" item="{{ $item->id }}" type="number" min="1" value="{{ $item->qty }}" aria-label="Quantity for {{ $item->product->name }}" {!! $item->product->stock->qty == 0 ? 'disabled' : '' !!}>
                                            <div class="btn-num-product-up flex-vert-center add-less-quantity" type="add" role="button" aria-label="Increase quantity"><i class="fas fa-plus" aria-hidden="true"></i></div>
                                        </div>
                                    </td>
                                    <td class="text-align-right">
                                        <span class="cart-total cart-line__total">@money($item->subtotal())</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <a href="{{ route('shop') }}" class="link-underline cart-continue"><i class="fas fa-arrow-left" aria-hidden="true"></i> Continue shopping</a>
                </div>

                <aside class="cart-summary-card" aria-labelledby="summary-title">
                    <h2 id="summary-title" class="cart-summary-card__title">Order summary</h2>
                    <dl class="cart-summary-card__rows">
                        <div><dt>Subtotal</dt><dd>@money($cart->total)</dd></div>
                        <div><dt>Shipping</dt><dd class="pdp-muted">Calculated at checkout</dd></div>
                    </dl>
                    <div class="cart-summary-card__total">
                        <span>Estimated total</span>
                        <span>@money($cart->total)</span>
                    </div>
                    <a href="{{ route('checkout.information') }}" class="btn btn-dark cart-summary-card__cta">Checkout</a>
                    <ul class="pdp-perks" role="list">
                        <li><i class="fas fa-rotate-left" aria-hidden="true"></i> Free returns within 30 days</li>
                        <li><i class="fas fa-lock" aria-hidden="true"></i> Secure checkout</li>
                    </ul>
                </aside>
            </div>
        @endif
    </div>
@endsection
