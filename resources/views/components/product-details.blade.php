<div class="additional-details">
    <ul>
        <li>
            <span class="span-col-1">Weight : </span>
            <span>
                @if ( $product->weigth != null )
                    {{ $product->weigth . 'kg' }}
                @endif
            </span>
        </li>
        <li>
            <span class="span-col-1">Dimensions : </span>                                
            <span>
                @if ( $product->dimensions != null)
                     <span>{{ $product->materials . 'cm' }}</span>
                @endif
            </span>


          
        </li>
        <li>
            <span class="span-col-1">Materials : </span>
            <span>{{ $product->materials }}</span>
        </li>  
        
         @foreach ($product->variants as $variant)
                 
        <li>                                  
           
            <span class="span-col-1">{{ $variant->varaints->name }} : </span>
                @foreach ($product->getAttr($product->id, $variant->variant_id) as $attribute)                                         
                    <span>{{ $attribute->attributes->value . ',' }}</span>                                                                   
                @endforeach   
           
        </li>
         @endforeach
       
   
    </ul>
</div>