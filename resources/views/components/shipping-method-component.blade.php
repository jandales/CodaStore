<div class="checkout-shipping-method mt-4">
    <h2 class="checkout-section-title mb-1">Shipping method</h2>
  
    @foreach ($shippingmethods as $key => $method)   
        <div class="panel {{ $key != $shippingmethods->count() - 1 ? 'border-b-0' : ''}}">
            <div class="flex space-between">
                <div class="flex gap10">                 
                    <input type="radio" class="shipping-input"  @if ($active == 0)  {{ $key == 0 ? 'checked' : '' }}
                    @else
                        {{ $method->id == $active ? 'checked' : '' }}
                    @endif
                      shippingmethod="{{$method->id}}"  id="shipping-method-{{ $method->id }}" name="shipping_method" value="{{ $method->id }}">
                    <label for="shipping-method-{{ $method->id }}" class="flex flex-column">
                        <span class="method">{{ $method->name }}</span> 
                        <span class="checkout-hint">{{ $method->description }}</span>
                    </label>                             
                </div>
                <span class="checkout-price">@money($method->amount)</span>
            </div>
        </div>
    @endforeach


   </div>