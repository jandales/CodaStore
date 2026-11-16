<section class="home-block" aria-labelledby="featured-title">
    <div class="home-block__head">
        <h2 id="featured-title" class="home-block__title">Featured pieces</h2>
        <a href="{{ route('shop.sort', ['featured-product']) }}" class="link-underline">View all</a>
    </div>
    <div class="product-grid">
        @foreach ($products as $product)
            @include('components.product-card', ['product' => $product])
        @endforeach
    </div>
</section>
