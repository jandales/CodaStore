<div class="bg-grey p-20">
    <div class="flex flex-column gap20">
        @foreach ($carts as $cart)
        <div class="cart-sm gap10">                    
            <div class="cart-image">
                <img class="img" src="/{{ $cart->product->imagePath }}" alt="" srcset="">
                <div class="cart-image-overlay flex-vert-center">
                    <span onclick="cartRemove(this)" data-id="${item.id}"><i class="fas fa-times"></i></span>
                </div>
            </div>
            <div class="w-12">
                <div class="flex space-between">
                    <span class="cart-item-name">{{$cart->product->name}}</span> 
                    <span class="cart-item-name">@money($cart->price)</span>   
                </div>                           
                <span class="cart-item-info">Qty: {{ $cart->qty  }}</span>  
                <ul class="cart-item-variant">    
                </ul> 
            </div>  
        </div>
        @endforeach
    </div>

    <div class="form-inline gap10 mt-2">
        <input type="text" placeholder="Promo Code" value="">
        <div class="btn btn-dark">Apply</div>
    </div>
    <div class=" mt-2">
        <div class="flex space-between">
            <span>Subtotal</span>
            <span>@money(cartSubtotal($carts))</span>
        </div>

        <div class="flex space-between mt-1">
            <span>Shipping</span>
            <span>@money(shippingFee())</span>
        </div>

        <div class="flex space-between mt-1">
            <span>Total</span>
            <span>@money(cartSubtotal($carts) + shippingFee() )</span>
        </div>
    </div>
</div>
