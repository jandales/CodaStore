<ul class="form-navigation">
    <li><a class="{{ $active = 'item' ? 'active' : '' }}" href="{{ route('product.edit',[$product]) }}" >Item Information</a></li>
    <li><a class="{{ $active = 'variants' ? 'active' : '' }}" href="{{ route('product.variants',[$product]) }}" >Variants</a></li>
    <li><a class="{{ $active = 'stock' ? 'active' : '' }}"href="{{ route('stock.edit',[$product])}}">Quantity</a></li>
    <li><a class="{{ $active = 'price' ? 'active' : '' }}"href="{{ route('price.edit',[$product])}}">Pricing</a></li>
    <li><a class="{{ $active = 'upload' ? 'active' : '' }}"href="{{ route('product.download',[$product]) }}">Image Upload</a></li>  
</ul>

