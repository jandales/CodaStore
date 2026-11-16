@extends('layout.front.app')

@section('content')
    <section class="home-hero" aria-labelledby="hero-title">
        <div class="home-hero__text">
            <span class="eyebrow">New season · {{ date('Y') }}</span>
            <h1 id="hero-title" class="home-hero__title">Quiet clothes,<br>made to last.</h1>
            <p class="home-hero__sub">Considered essentials in natural fabrics. Cut simply, worn often.</p>
            <div class="home-hero__actions">
                <a href="{{ route('shop') }}" class="btn btn-dark">Shop new in</a>
                <a href="#collections" class="btn btn-default">Explore collections</a>
            </div>
        </div>
        <div class="home-hero__media">
            <img src="/img/front/hero-1800.jpg" alt="Folded knitwear, denim and shoes on dark wooden shelves" width="1800" height="1200" fetchpriority="high">
        </div>
    </section>

    <div class="container home-sections">
        <x-collection-component/>
        <x-featured-product-component/>
    </div>

    <section class="home-values" aria-label="Why shop with us">
        <div class="container home-values__grid">
            <div>
                <i class="fas fa-rotate-left" aria-hidden="true"></i>
                <h2>Free returns</h2>
                <p>Change your mind within 30 days.</p>
            </div>
            <div>
                <i class="fas fa-leaf" aria-hidden="true"></i>
                <h2>Natural fabrics</h2>
                <p>Cotton, linen and wool, chosen to last.</p>
            </div>
            <div>
                <i class="fas fa-lock" aria-hidden="true"></i>
                <h2>Secure checkout</h2>
                <p>Cards, PayPal and cash on delivery.</p>
            </div>
        </div>
    </section>
@endsection
