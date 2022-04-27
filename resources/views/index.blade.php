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
            <h1 class="home-title">Collections</h1>
            <div class="collection">
                @foreach ($collection as $key => $item)
                    <div class="item">
                        <h3 class="title">{{$item->name}}</h3>                      
                        <img src="{{$item->image}}" alt="" srcset=""> 
                        <a href="/shop" class="link-shop-now">Shop now</a> 
                    </div>
                @endforeach
            </div>
            <h1 class="home-title">Feature Products</h1>

            <div class="featured-product">
                @foreach ($products as $product)
                    <div class="items mb-2">                                  
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
                @endforeach                  
                
            </div>

        </div>  
        
      
      



       
        
@endsection




  