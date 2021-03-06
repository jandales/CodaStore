<section class="splide splide-collection" aria-label="Splide Basic HTML Example">
    <h1 class="home-title text-center">Collections</h1>
    <div class="collection">                    
        <div class="splide__track">
            <ul class="splide__list splide-feature-product">
                @foreach ($collection as $key => $item)
                    <li class="splide__slide">
                        <div class="item">
                            <h3 class="title capitalize">{{$item->name}}</h3>                      
                            <img src="{{$item->image}}" alt="" srcset=""> 
                            <a href="{{ route('shop.category',[$item])}}" class="link-shop-now">Shop now</a> 
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
      
    </div>
</section>   