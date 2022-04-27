@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Products</h1>
</div>
<div class="row" >
    <div class="panel-table m-t-2 w-12">
        <div class="panel-header">            
        </div>
           
                <div class="toolbar justify-content-space-between action-toolbar hidden"> 
                    <label class="title selected-items">2 item Selected</label>
                    <div class="btn-action">
                        <span onclick="url('post','{{route('admin.products.status.updates',[1])}}')" class="btn btn-light"><i class="fas fa-upload"></i></span>
                        <span onclick="url('post','{{route('admin.products.status.updates',[0])}}')" class="btn btn-light"><i class="fas fa-download"></i></span>                           
                        <span  onclick="url('post','{{route('admin.products.destroys')}}')" class="btn btn-light"><i class="fas fa-trash"></i></span>
                        <span  onclick="clearSelection()" class="btn btn-light"><i class="fas fa-times"></i></span> 
                    </div>
                </div> 
                <div class="toolbar default-toolbar space-between"> 
    
                           <div class="flex items-center">
                                <label for="" class="label-width">Filter :</label>
                                <select name="filter" id="filter">
                                    <option value="">-- Select --</option>
                                    <option  data-url="{{ route('admin.products.filter',['all'])}}"  value="all" }>All</option>
                                    <option  data-url="{{ route('admin.products.filter',['published'])}}"  value="published" >Published</option>
                                    <option  data-url="{{ route('admin.products.filter',['featured-products'])}}"  value="featured-products" >Feautured Product</option>
                                    <option   data-url="{{ route('admin.products.filter',['unpublished'])}}" value="unpublished" >Unpublished</option>
                                </select>
                           </div>
                           
                           <div class="flex gap20">
                                <form id="formSearch" action="{{ route('admin.products.search') }}" method="get">
                                    @csrf
                                    <div class="search-input"> 
                                        <span class="icon-left"></span>                           
                                        <input type="text" placeholder="Search" name="keyword">
                                        <span class="icon-right" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>
                                    </div>                     
                                </form>
                            
        
                                <a href="/admin/products/add" class="btn btn-primary mr-2">
                                    <span><i class="fas fa-plus-circle"></i></span>
                                    Add item
                                </a>
                           </div>
                     
                </div> 

                <form id="form" method="post" action="">  
                    @csrf
                    <table class="table">                          
                        <thead>
                            <tr>
                                <th class="column-1">                   
                                    <div class="checkbox">
                                                <input type="checkbox" id="parentCheckbox" name="checkbox" >
                                    </div>
                                </th>
                                <th class="column-image"></th>
                                <th class="column-product">Product Name</th>                              
                                <th class="column-base">Categories</th>                                          
                                <th class="column-base">Inventory</th>
                                <th class="column-base">Price</th>
                                <th class="column-base">Status</th>
                                <th class="column-action"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ( count($products) != 0 )
                                @foreach ($products as $product) 
                                    <tr>
                                        <td class="column-1">
                                            <div class="checkbox">
                                                <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $product->id }}">
                                            </div>
                                        </td>                                
                                        <td class="column-image">
                                            <div class="image-50"> <img src="\{{$product->imagePath}}"></div>
                                        </td>   
                                        <td class="column-product">
                                            <div class="product-image-container">                                               
                                                <div class="image-description ml-0">
                                                    <a href="{{ route('shop.product', [$product ]) }}" class="item-name">{{ $product->name}}</a>
                                                </div>                           
                                            </div>
                                        </td>   
                                        <td class="column-base"><p>{{ $product->category->name }}</p></td>
                                        <td class="column-base"><p>{{ $product->stock->qty . " stocks" }}</p></td>
                                        <td class="column-base"><p>@money($product->regular_price)</p></td> 
                                        <td class="column-base"><p class="{{ $product->status }} capitalized">{{ $product->status() }}</p></td>
                                        <td class="column-action"> 
                                            <div class="table-action">
                                                <ul>   
                                                    <li>                                   
                                                        <a href="{{route('admin.products.edit',[$product])}}">
                                                            <span class="span">
                                                                <i class="fas fa-pen"></i>  
                                                            </span>                                                                           
                                                        </a>
                                                    </li>                               
                                                    <li class="table-dropdown">
                                                        <span class="span">                                      
                                                                <i class="fas fa-circle"></i>
                                                                <i class="fas fa-circle"></i>
                                                                <i class="fas fa-circle"></i>
                                                        </span>
                                                        <ul class="table-dropdown-list">
                                                            <li>
                                                                <span onclick="published('{{ route('admin.products.status.update',[$product]) }}')">
                                                                    <i class="fas {{ $product->status == 'published' ? 'fa-download' : 'fa-upload'}}"></i>
                                                                    <span class="ml-1">@if($product->status == 1) {{ 'Unpublished' }} @else  {{ 'Published' }} @endif</span>
                                                                </span>
                                                            </li>                                                                             
                                                            <li>
                                                                <a href="#">
                                                                    <i class="far fa-list-alt"></i>
                                                                    <span class="ml-1">Inventory</span>
                                                                </a>
                                                            </li>
                                                                                                                   
                                                            <li>
                                                            <a  onclick="urldelete('{{route('admin.products.destroy',[$product])}}')">
                                                                    <i class="far fa-trash-alt"></i>
                                                                    <span class="ml-1">Delete</span>
                                                                </a>  
                                                            </li>                                    
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td>No found Record</td></tr>
                            @endif
                        </tbody>
                    </table>
                </form>
        
            <div class="mt-2 mb-2 right mr10">
                {{ $products->links() }}
            </div>

            <form  id="deleteform" method="POST">
                @csrf
                @method('delete')
            </form>

            <form id="updateform" method="POST">
                @csrf
                @method('PUT')
            </form>
        
            
      
    </div>

    <script>
const btnfilter = document.getElementById('filter');
btnfilter.addEventListener('change', function() {
    const url = btnfilter.options[btnfilter.selectedIndex].getAttribute('data-url');
    if(!url) return;   
    window.location.href = url;
})

    </script>
   
</div>






@endsection