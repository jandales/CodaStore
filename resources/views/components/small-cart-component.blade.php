<div class="bg-grey p-20">
    <div class="flex flex-column gap20">
        @foreach ($cart->items as $cart_item)
        <div class="cart-sm gap10">                    
            <div class="cart-image">
                <img class="img" src="/{{ $cart_item->product->imagePath }}" alt="" srcset="">
                <div class="cart-image-overlay flex-vert-center">
                    <span class="cart-item-remove" data-id="{{$cart_item->id}}"><i class="fas fa-times"></i></span>
                </div>
            </div>
            <div class="w-12">
                <div class="flex space-between">
                    <span class="cart-item-name">{{$cart_item->product->name}}</span> 
                    <span class="cart-item-name">@money($cart_item->product->regular_price)</span>   
                </div>                           
                <span class="cart-item-info">Qty: {{ $cart_item->qty  }}</span>  
                <ul class="cart-item-variant">    
                </ul> 
            </div>  
        </div>
        @endforeach
    </div>

    <div id="coupon-form" class="form-inline gap10 mt-2 {{ $cart->coupon_id != null ? 'hidden' : ''}}">
        <input type="text" id="coupon_code" validator-input="coupon_code" name="coupon_code" placeholder="Coupon Code">
        <button id="btn-coupon-apply" class="btn btn-dark w-3">Apply</button>   
    </div>

    <small class="validator-text" validator-for="coupon_code">Please Enter Card Name</small>
    <div class=" mt-2">
        <div class="flex space-between py-1 bor12">
            <span>Items</span>
            <span id="cart-items-count"class="text-align-right">{{ $cart->totalItems() }}</span>  
        </div>
        
        <div class="flex space-between mt-1  py-1 bor12">
            <span>Subtotal</span>
            <span  id="subtotal">@money($cart->total)</span>
        </div>
      

        <div class="flex space-between py-1 bor12">
            <span>Shipping</span>
            <span class="span-shipping-fee">@if ($shipping_charge != 0) @money($shipping_charge) @else {{ 'Shipping charge caclulated Next step' }} @endif</span>
        </div>

        
        <div id="has-coupon" class="flex flex-column bor12 py-1 {{ $cart->coupon_id == null ? 'hidden' : ''}}">   

            <div class="flex gap10">
                <span>Coupon :</span>
                <div class="flex flex-column flex-auto">
                    <div class="flex space-between">
                        <span id="coupon-code">{{ $cart->couponName('name') }}</span>
                        <span id="coupon-amount"  class="right">@money($cart->discount)</span>
                    </div>
    
                    <div class="flex space-between mt10">
                        <span id="coupon-description">{{ $cart->couponName('description') }}</span>
                        <span id="btn-coupon-remove"  class="right text-danger pointer">Remove</span>
                    </div> 
                </div> 
               
            </div>
                                             

        </div>

        <div class="flex space-between mt-1">
            <span>Total</span>
            <span id="grand-total" data={{$cart->grandTotal()}} >@money($cart->grandTotal())</span>
        </div>
    </div>
</div>
