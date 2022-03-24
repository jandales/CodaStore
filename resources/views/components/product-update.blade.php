<div class="modal-content">
       <div class="modal-heading">
            <h1>Product Overview</h1>
            <span  class="modal-close">&times</span>                   
       </div>
        <div class="modal-body">
            <div class="panel-view">   
                <div class="thumbnails-wrapper">
                    <div class="thumbnails">
                        @foreach ($product->photos as $i => $photo ) 
                            <div class="thumbnail">
                                <img class="thumbnail-{{ $i }}" src="/{{ $photo->image }}" alt="">
                                <div class="thumbnail-overlay"></div>
                            </div>  
                         @endforeach 
                    </div>
                </div>        
                <div class="main-image-wrapper">
                    <div class="main-image" >
                        <img  src="/{{$product->imagePath }}" alt="">
                    </div>
                </div>

                <div class="description">
                    <p class="title">{{ $product->name}}</p>
                    <ul  rating ="{{ $product->rating() }}" class="productRating rate-stars mt10"></ul>
                    <p class="price mt10">@money($product->prices->selling)</p>                
                    <p class="info mt10">{{ $product->description }}</p>
                    <br>    
                    <ul class="variants">
                        @foreach ($product->variants as $variant)
                        <li>
                            <div class="variant mb-1">
                                    <div class="attribute-name">
                                        <p class="capitalize">{{ $variant->varaints->name }} :</p>
                                    </div>                        
                                    <ul>
                                        @foreach ($product->getAttr($variant->variant_id) as $attribute) 
                                                <li  class="variant-options" name="{{ $variant->varaints->name  }}" value="{{ $attribute->attributes->value }}">
                                                    <div class="attribute {{ $variant->varaints->name != 'color' ? 'capitalize' : ''}} {{  $attribute->attributes->value }}">
                                                        @if ($variant->varaints->name != 'color') {{ $attribute->attributes->value }} @endif
                                                    </div>
                                                </li>
                                        @endforeach 
                                    </ul>    
                            </div>
                        </li>
                        @endforeach  
                    </ul>

                    <br>
                    <div class="product cart-form-group">
                        <div class="btn-num-product-down flex-vert-center add-minus-quantity" onclick="quantity('minus')"> <i class="fas fa-minus"></i></div>
                        <input class="quantity-input num-product bg-grey"  name="qty" value="1" type="number">
                        <div class="btn-num-product-up flex-vert-center add-minus-quantity" onclick="quantity('add')"> <i class="fas fa-plus"></i></div>
                    </div>
                    <br>
                <div class="view-button">   
                <form action="{{ route('wishlist.product.update',[$wishlist])}}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" value="1" name="qty" class="quantity">
                        <input type="hidden"  name="properties" class="properties">
                        <button class="button updateBtn">UPDATE</button>    
                </form>    
                </div>
                </div>
            </div>
        </div>
    </div>


 


