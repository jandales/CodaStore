<section class="splide splide-featured-products mb-5" aria-label="Splide ">
    <h1 class="home-title">Feature Products</h1>
    <div class="featured-product">                         
        <div class="splide__track">
            <ul class="splide__list splide-feature-product">
                @foreach ($products as $product)
                <li class="splide__slide">
                    <div class="items">                                  
                        <div class="item">
                            <a href="{{ route('shop.product',[ $product ] )}}">
                                <img src="/{{ $product->imagePath }}">      
                            </a>                              
                        </div>
                        <div class="item-description">
                            <div class="product-detail-wrapper">
                                <a href="{{ route('shop.product',[ $product ] )}}">{{ $product->name }}</a>
                                <label class="mt-1">@money($product->regular_price)</label>
                            </div>
                        </div>
                    </div>  
                </li>
                @endforeach                         
            </ul>
        </div>
    </div>    
</section>