@extends('layout.front.app')

@section('content')
    

    <!--Featured Product-->
        <div class="container">  
           
    
            <div class="productPage mt-2">    
                <div class="search-page">
                    <h2>Search results</h2> 
                    <form  action="{{ route('search') }}" method="get">
                        <div class="input-search">                        
                            <input type="text"  name="keyword" placeholder="Search Here...." value="{{$keyword}}">
                            <span class="close-search"><i class="fa fa-search" aria-hidden="true"></i></span>
                        </div>
                    </form>  
                </div>         
                {{-- <label>Search Found"{{ $products->count() }}"</label> --}}
                <div class="product-nav">              
                    <nav>
                        <ul class="ul-product-categories">
                            <li><a href="{{ route('shop')}}">All Products</a></li>
                            @foreach (categories() as $category)                            
                                <li><a href="{{ route('shop.category',[$category])}}">{{ $category->name }}</a></li>
                            @endforeach          
                        </ul>    
                        <div class="filter-search-container align-items-center" style="width: 280px">
                            <span style="flex:auto; flex-basis:100px;">Sort By:</span>
                            <select name="sortBy" id="sorting">
                                <option value="{{ route('shop.sort',['all']) }}" {{ (request()->is('shop')) ? 'selected' : ''}}>All</option>
                                <option value="{{ route('shop.sort',['a-z']) }}" {{ (request()->is('shop/sort-by/a-z')) ? 'selected' : ''}}>Alphabetical: A to Z</option>
                                <option value="{{ route('shop.sort',['z-a']) }}" {{ (request()->is('shop/sort-by/z-a')) ? 'selected' : ''}}>Alphabetical: Z to A</option>
                                <option value="{{ route('shop.sort',['new-to-old']) }}" {{ (request()->is('shop/sort-by/new-to-old')) ? 'selected' : ''}}>New to Old</option>
                                <option value="{{ route('shop.sort',['old-to-new']) }}" {{ (request()->is('shop/sort-by/old-to-new')) ? 'selected' : ''}}>Old to New</option>
                                <option value="{{ route('shop.sort',['low-to-high']) }}" {{ (request()->is('shop/sort-by/low-to-high')) ? 'selected' : ''}}>Price: Low to High</option>
                                <option value="{{ route('shop.sort',['high-to-low']) }}"{{ (request()->is('shop/sort-by/high-to-low')) ? 'selected' : ''}}>Price: High to Low</option>
                            </select>
                         
                        
                        </div>
                      
                    </nav>
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
                                            <label class="mt10 text-dark">@money($product->regular_price)</label>
                                        </div>
                                        {{-- <div class="add-wishlist-wrapper">                                            
                                            @auth                                            
                                                 <span class="add-wish-list {{ $product->isWishlisted() ? 'ctheme' : '' }} "   data="{{ $product->id}}"><i class="{{ $product->isWishlisted() ? 'fas fa-heart' : 'far fa-heart' }} "></i></span>                                                      
                                            @endauth
                                            @guest
                                                <span class="add-wish-list" data="{{ $product->id}} "><i class="far fa-heart"></i></span>
                                            @endguest                                            
                                        </div>                                        --}}
                                    </div>
                                </div>
                        @endforeach
                    @endif
                   
                </div>
                    @if (count($products) != 0)
                        <div class="pagination-wrapper">
                            {{ $products->links() }}
                        </div>
                    @endif
             
            </div>
    
         
        </div>
        
        <script src="/js/product/rating.js"></script>
        <script src="/js/product/addwishlist.js"></script>
        <script>
            document.getElementById('sorting').onchange = function(){
                const id = this.selectedIndex;

                url = this.options[id].value;
                window.location.href = url;
            }

        </script>
    
    
     @endsection