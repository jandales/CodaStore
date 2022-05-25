@extends('layout.front.app')

@section('content')  
    <!--Featured Product-->
        <div class="container">   
            @if (session('success'))
                 <div class="alert alert-success">{{session('success')}}</div>
            @endif   
            <div class="panel-view">
                <div class="product-images">
                    <div class="thumbnails-wrapper">             
                        <ul>                        
                         @foreach ( $product->photos as $i => $photo )                              
                            <li>
                                <div class="thumbnail">
                                    <img class="thumbnail-{{ $i }}" src="/{{ $photo->path }}" alt="">
                                    <div class="thumbnail-overlay"></div>
                                </div>
                            </li>     
                         @endforeach                   
                        </ul>
                     </div>
                     <div class="full-image">
                        <img id="fullImage" src="/{{ $product->imagePath }}" alt="">
                        <span class="arrow arrow-left align-items"><i class="fas fa-chevron-left"></i></span>                            
                        <span class="arrow arrow-right align-items"><i class="fas fa-chevron-right"></i></span>
                    </div>
                </div> 
                <div class="description">                   
                    <h4 class="title">{{ $product->name }}</h4>
                     <div class="inline mt-1">                          
                         <x-rating :rating="$product->rating()" />                        
                         <p class="ml-1">{{$product->reviews->count()}} Reviews</p>
                     </div>
                    <p class="price mt-1">@money($product->regular_price)</p>        
                    <p class="details mt-1">{{ $product->short_description }}</p>        
                    <br>

                    <ul class="variants">
                        <x-variants :product="$product" />  
                    </ul>

                    <span>{{ $product->minQty() }}</span>

                    <div class="mt-2"> 
                        <div class="product cart-form-group">
                            <div class="btn-num-product-down flex-vert-center btn-add-minus" type="minus" > <i class="fas fa-minus"></i></div>
                            <input  id="quantity-input" class=" num-product bg-grey"  name="qty" value="1" type="number">
                            <div class="btn-num-product-up flex-vert-center btn-add-minus" type="add"> <i class="fas fa-plus"></i></div>
                        </div>
                        <div class="mt-2">                                         
                            <button id="cart-button" data-id="{{ $product->encryptedId() }}" url="{{route('cart.store',[$product->encryptedId()])}}" class="btn btn-dark btn-add-cart">Add to Cart</button>                       
                        </div>
                    </div>             
                </div>
            
            </div>
            
            <div class="product-additional-info-wrapper mt-7 mb-7">
                <div class="tabs tab-product-detail">
                    <ul class="tab-navigation">
                        <li><a  class="tabs-button" data-for-tab="1" >Description</a></li>                  
                        <li><a  class="tabs-button" data-for-tab="2" >Reviews ( {{ $product->reviews->count() }} )</a></li>                     
                    </ul>
                    <div class="tabs-content" data-tab= "1">
                        <div class="additional-description">
                            <p>{{ $product->long_description }}</p>
                        </div>
                    </div>  
                    <div class="tabs-content" data-tab= "2">
                        <x-product-review :product="$product" />
                    </div>
                </div>
            </div>

            <x-featured-product-component/>


        </div>
@endsection