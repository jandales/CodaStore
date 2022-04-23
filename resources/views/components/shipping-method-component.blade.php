<div class="checkout-shipping-method mt-4">
    <h2 class="uppercase  mb-1">Shipping Method</h2>
  
    @foreach ($shippingmethods as $key => $method)   
        <div class="panel {{ $key != $shippingmethods->count() - 1 ? 'border-b-0' : ''}}">
            <div class="flex space-between">
                <div class="flex gap10">                 
                    <input type="radio"  @if ($active == 0)  {{ $key == 0 ? 'checked' : '' }}
                    @else
                        {{ $method->id == $active ? 'checked' : '' }}
                    @endif
                      shippingmethod="{{$method->id}}"  style="margin-top: 5px" name="shipping_method" value="{{ $method->id }}">
                    <div class="flex flex-column">
                        <span class="method">{{ $method->name }}</span> 
                        <span>{{ $method->description }}</span>  
                    </div>                             
                </div>
                <a href="">@money($method->amount)</a>
            </div>
        </div>
    @endforeach


   </div>