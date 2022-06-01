<input type="hidden" name="attributes" value="{{$product->attributes}}">
<input type="hidden" name="variants" value="{{$product->variants}}">

@foreach($product->attributes as $item)
    <li>
        <div class="variant mb-1">
                <div class="attribute-name">
                    <p class="capitalize">{{ $item->attributes->name }} :</p>
                </div>                        
                <ul>
                    @foreach ($product->variants as $variant) 
                        @if ($item->attribute_id ==  $variant->attribute_id )
                             <li  class="variant-options" name="{{ $item->attributes->name }}" value="{{ $variant->name }}">
                                <div class="attribute  capitalize">                                
                                        {{ $variant->name }}                                   
                                </div>
                            </li>
                        @endif
                   
                            {{-- <li onclick="selectVaraints(this)"   class="variant-options" name="{{ $variant->varaints->name }}" value="{{ $attribute->attributes->value }}">
                                <div class="attribute {{ $variant->varaints->name != 'color' ? 'capitalize' : ''}} {{  $attribute->attributes->value }}">
                                    @if ($variant->varaints->name != 'color') 
                                    {{ $attribute->attributes->value }}
                                    @endif
                                </div>
                            </li> --}}
                    @endforeach  
                </ul>    
        </div>
    </li>
@endforeach