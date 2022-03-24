@extends('layout.front.app')

@section('content') 
        <div class="container homepage">
                        <div class="row">
                            <div class="col-1">
                                <p>Men Collection 2021</p>
                                <h1>New Arrivals</h1>
                                <a href="/shop" class="btn cffffff bgff523b mt-2">Shop now</a>
                            </div>
                            <div class="col-2">
                                <img src="/img/front/main.png">
                            </div>
                        </div>
        </div>      
    <!--Featured Product-->
        <div class="container">
            <div class="product">            
                <div class="product-nav">
                    <h1>Featured Product</h1>                    
                </div>
                <div class="product-row">
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
                    
                </div>

                @if (count($products) != 0)
                <a href="#" class="shopnow">Load More</a>
                @endif
            </div>

        </div>  
        
      
      



       
        
@endsection




  