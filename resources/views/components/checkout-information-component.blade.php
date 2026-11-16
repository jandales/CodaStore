<div class="checkout-information">
    <div class="panel border-b-0">
        <div class="flex space-between">
            <div class="flex">
                <span class="checkout-info-label">Contact</span>
                <span class="span-email">{{ session('email') }}</span>
            </div>
            <a href="{{route('checkout.information')}}" class="change">Change</a>
        </div>
    </div> 
<div class="panel {{(request()->is('checkout/payment')) ? 'border-b-0' : ''}}">
        <div class="flex space-between">
            <div class="flex">
                <span class="checkout-info-label">Ship to</span>
                <span class="span-ship-to">{{ session('shipping_address')['street'] . " " . session('shipping_address')['city'] . " " . session('shipping_address')['region'] . " " .  session('shipping_address')['country'] }}</span>
            </div>
            <a href="{{route('checkout.information')}}" class="change">Change</a>
        </div>
    </div> 
    @if(request()->is('checkout/payment'))
        <div class="panel">
            <div class="flex space-between">
                <div class="flex">
                    <span class="checkout-info-label">Method</span>
                    <span class="span-method">{{ session('shipping_method')['name'] }}</span>
                </div>
                <a href="{{route('checkout.shipping')}}" class="change">Change</a>
            </div>
        </div> 
    @endif
</div>