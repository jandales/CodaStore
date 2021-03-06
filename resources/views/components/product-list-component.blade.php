<div class="product-nav">              
    <nav>
        <div class="category-hamburger">
            <span><i class="fa-solid fa-bars"></i></span>
            <label for="">Collections</label>
        </div>  
        <ul class="ul-product-categories">
            <div class="close-category">
                <span><i class="fa-solid fa-times"></i></span>
                <label for="">Collections</label>
            </div> 
            <li><a href="{{ route('shop')}}" class="capitalize">All Collections</a></li>
            @foreach (categories() as $category)                            
                <li><a href="{{ route('shop.category',[$category])}}" class="capitalize">{{ $category->name }}</a></li>
            @endforeach          
        </ul>    
        <div class="filter-search-container align-items-center" style="width: 280px">
            <span style="flex:auto; flex-basis:100px;">Sort By:</span>
            <select name="sortBy" id="sorting">
                <option value="{{ route('shop.sort',['all']) }}" {{ (request()->is('shop')) ? 'selected' : ''}}>All</option>
                <option value="{{ route('shop.sort',['featured-product']) }}"{{ (request()->is('shop/sort-by/featured-product')) ? 'selected' : ''}}>Featured Product</option>
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
                        <a href="{{ route('shop.product',[$product->encryptedId() ] )}}">
                            <img src="{{ $product->imagePath }}">      
                        </a>                                                                         
                    </div>
                    <div class="item-description">
                        <div class="product-detail-wrapper">
                            <a href="{{ route('shop.product',[$product->encryptedId() ] )}}">{{$product->name}}</a>                                       
                            <label class="mt10 text-dark">@money($product->regular_price)</label>
                        </div>                                       
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