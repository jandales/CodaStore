@foreach ($product->variants as $variant)
<li>
    <div class="variant mb-1">
            <div class="attribute-name">
                <p class="capitalize">{{ $variant->varaints->name }} :</p>
            </div>                        
            <ul>
                @foreach ($product->getAttr($variant->variant_id) as $attribute) 
                        <li onclick="selectVaraints(this)"   class="variant-options" name="{{ $variant->varaints->name }}" value="{{ $attribute->attributes->value }}">
                            <div class="attribute {{ $variant->varaints->name != 'color' ? 'capitalize' : ''}} {{  $attribute->attributes->value }}">
                                @if ($variant->varaints->name != 'color') 
                                   {{ $attribute->attributes->value }}
                                @endif
                            </div>
                        </li>
                @endforeach 
            </ul>    
    </div>
</li>
@endforeach