@extends('layout.front.app')

@section('content')
    

    <!--Featured Product-->
        <div class="container">    
    
            <div class="productPage mt-5">
              
                <div class="product-nav">              
                    <nav>
                        <ul class="ul-product-categories">
                            <li><a href="{{ route('shop')}}">All Products</a></li>
                            @foreach (categories() as $category)                            
                                <li><a href="{{ route('shop.category',[$category])}}">{{ $category->name }}</a></li>
                            @endforeach          
                        </ul>    
                        <div class="filter-search-container">
                            <form action="{{ route('shop.search')}}" method="post">    
                                @csrf              
                                <div class="search-in-page">                                             
                                    <input type="text" name="input" placeholder="Search product here" value="{{old('input')}}">  
                                    <button><i class="fas fa-search"></i></button>                                              
                                </div>
                            </form>
                            <button class="btn-filter" ><span><i class="fas fa-filter"></i></span> Filter</button>
                        
                        </div>
                      
                    </nav>
                </div>

                <div id="filter-wrapper">                                
                    <div class="filter">
                                <div class="sort">
                                    <p>Sort by</p>
                                <ul>
                                    <li><a href="#">Default</a></li>
                                    <li><a href="#">Popularity</a></li>
                                    <li><a href="#">Average rating</a></li>
                                    <li><a href="{{ route('shop.filter.latest',['high'])}}">Newness</a></li>
                                    <li><a href="{{ route('shop.filter.price.order',['low'])}}">Price: Low to High</a></li>
                                    <li><a href="{{ route('shop.filter.price.order',['high'])}}">Price: High to Low</a></li>  
                                </ul>
            
                            </div>
            
                            <div class="price">
                                <p>Price</p>
                        
                                <ul>
                                    <li><a href="{{ route('shop')}} ">All</a></li>
                                    <li><a href="{{ route('shop.filter.price',['0-50'])}}">$0 - $50</a></li>
                                    <li><a href="{{ route('shop.filter.price',['50-100'])}}">$50 - $100</a></li>
                                    <li><a href="{{ route('shop.filter.price',['100-200'])}}">$100 - $200</a></li>
                                    <li><a href="{{ route('shop.filter.price',['200-5000'])}}">$150+</a></li> 
                                </ul>
            
                            </div>
            
                            <div class="color">
                                <p>Color</p>
                                <ul>
                                    <li><a href="{{ route('shop.filter.attribute',['red']) }}">Red</a></li>
                                    <li><a href="{{ route('shop.filter.attribute',['blue']) }}">Blue</a></li>
                                    <li><a href="{{ route('shop.filter.attribute',['black']) }}">Black</a></li>
                                    <li><a href="{{ route('shop.filter.attribute',['red']) }}">Grey</a></li>
                                    <li><a href="{{ route('shop.filter.attribute',['white']) }}">White</a></li>                      
                                </ul>
                            </div>
            
            
                            <div class="ul-tags">
                                <p>Tags</p>
                                <div class="tags">
                                    <a class="tag" href="{{ route('shop.filter.tags',['classic']) }}">Fashion</a>
                                    <a class="tag" href="{{ route('shop.filter.tags',['classic']) }}">Lifestyle</a>
                                    <a class="tag" href="{{ route('shop.filter.tags',['classic']) }}">Crafts</a>
                                    <a class="tag" href="{{ route('shop.filter.tags',['classic']) }}">Streetstyle</a>
                                    <a class="tag" href="{{ route('shop.filter.tags',['classic']) }}">Domin</a> 
                                                    
                                </div>
                            </div>
                    
                    </div>
                </div>    
              
                <div class="product-row">
                    @if (count($products) == 0)
                        <label> No item found</label>
                    @else
                        @foreach ($products as $product)
                                <div class="items mb-2">                                  
                                    <div class="item">
                                        <img src="/{{ $product->imagePath }}">                                        
                                    </div>
                                    <div class="item-description">
                                        <div class="product-detail-wrapper">
                                            <a href="{{ route('shop.product',[ $product ] )}}">{{ $product->name }}</a>                                       
                                            <label class="mt-1">@money($product->regular_price)</label>
                                        </div>
                                        <div class="add-wishlist-wrapper">                                            
                                            @auth                                            
                                                 <span class="add-wish-list {{ $product->isWishlisted() ? 'ctheme' : '' }} "   data="{{ $product->id}}"><i class="{{ $product->isWishlisted() ? 'fas fa-heart' : 'far fa-heart' }} "></i></span>                                                      
                                            @endauth
                                            @guest
                                                <span class="add-wish-list" data="{{ $product->id}} "><i class="far fa-heart"></i></span>
                                            @endguest                                            
                                        </div>                                       
                                    </div>
                                </div>
                        @endforeach
                    @endif
                   
                </div>
                    @if (count($products) != 0)
                        <a href="#" class="shopnow">Load More</a>
                    @endif
             
            </div>
    
         
        </div>
        
        <script src="/js/product/rating.js"></script>
        <script src="/js/product/addwishlist.js"></script>

    
    
     @endsection