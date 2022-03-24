
         <div class="panel-view">   
             <div class="thumbnails-wrapper">
                 <div class="thumbnails">
                     @foreach ($product->photos as $i => $photo ) 
                         <div class="thumbnail paddingbot10">
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
                 <div class="mt10">
                    <x-rating :rating="$product->rating()"/>
                </div> 
                 <p class="price mt10">@money($product->prices->selling)</p>                
                 <p class="info mt10">{{ $product->description }}</p>
                 <br>    
                 <ul class="variants">
                    <x-variants :product="$product" />                    
                 </ul>
                 <br>
                 <div class="product cart-form-group">
                     <div class="btn-num-product-down flex-vert-center add-minus-quantity" onclick="quantity('minus')"> <i class="fas fa-minus"></i></div>
                     <input class="quantity-input num-product bg-grey"  name="qty" value="1" type="number">
                     <div class="btn-num-product-up flex-vert-center add-minus-quantity" onclick="quantity('add')"> <i class="fas fa-plus"></i></div>
                 </div>
                 <br>
                 <div class="alert alert-danger flex space-between hidden">                     
                    <span class="alert-message"></span>
                    <span onclick="closeAlertMessage(this)"><i class="fas fa-times"></i></span>
                 </div>
             <div class="view-button">   
                <form id="form-to-cart" data-id="{{$product->id}}"  action="{{ route('wishlist.product.update',[$wishlist])}}"  method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" value="1" name="qty" class="quantity">
                        <input type="hidden"  name="properties" class="properties">
                        <button onclick="submitToCart('form-to-cart')" class="button updateBtn">Add to Cart</button>    
                </form>    
             </div>
             </div>
         </div>
  






   
