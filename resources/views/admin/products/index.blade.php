@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Products</h1>
</div>

@if(session('success'))
<br>
    <div class="alert alert-success">{{ session('success') }}</div>    
@endif
   
<div class="row" >
    <div class="panel-table m-t-2 w-12">
        <div class="panel-header">            
        </div> 
                <div class="toolbar justify-content-space-between action-toolbar hidden"> 
                    <label class="title selected-items">2 item Selected</label>
                    <div class="btn-action">
                        <span data-onClick data-url={{route('admin.products.status.updates',[1])}} class="btn btn-light"><i class="fas fa-upload"></i></span>
                        <span data-onClick data-url={{route('admin.products.status.updates',[0])}} class="btn btn-light"><i class="fas fa-download"></i></span>                           
                        @can('delete', $products[0])
                            <span  id="deleteSelectedProducts" data-url="{{route('admin.products.destroys')}}" class="btn btn-light"><i class="fas fa-trash"></i></span>
                        @endcan
                        <span  id="clear-selection" class="btn btn-light"><i class="fas fa-times"></i></span> 
                    </div>
                </div> 
                <div class="toolbar default-toolbar space-between"> 
    
                           <div class="flex items-center">
                                <label for="" class="label-width">Filter :</label>
                                <select name="filter" id="filter">
                                    <option  data-url="{{ route('admin.products.filter',['all', 'all'])}}"  value="all" {{ $filter == 'all' ? 'selected' : ''}}>All</option>
                                    <option  data-url="{{ route('admin.products.filter',['status',1])}}"  value="published" {{ $filter == 'status=published' ? 'selected' : ''}}>Published</option>
                                    <option  data-url="{{ route('admin.products.filter',['featured', 1])}}"  value="featured" {{ $filter == 'featured' ? 'selected' : ''}}>Feautured Product</option>
                                    <option  data-url="{{ route('admin.products.filter',['status', 0])}}" value="unpublished" {{ $filter == 'status=unpublished' ? 'selected' : ''}}>Unpublished</option>
                                </select>
                           </div>
                           
                           <div class="flex gap20">
                                <form id="formSearch" action="{{ route('admin.products.search') }}" method="get">                                    
                                    <div class="search-input">
                                        <span class="icon-left" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>                           
                                        <input type="text" placeholder="Search" name="keyword">
                                        <a href="{{route('admin.products')}}" class="{{ $keyword ?? 'hidden'}}"><span class="icon-right"><i class="fas fa-times"></i></span></a>
                                    </div>                     
                                </form>   
                                <a href="/admin/products/add" class="btn btn-primary mr-2">
                                    <span><i class="fas fa-plus-circle"></i></span>
                                    Add item
                                </a>
                           </div>
                     
                </div> 

              
                    <table class="table">                          
                        <thead>
                            <tr>
                                <th class="column-1">    
                                    <form id="form-table" method="post">  
                                        @csrf               
                                        <div class="checkbox">
                                                    <input type="checkbox" id="parentCheckbox" name="checkbox" >
                                        </div>
                                    </form>
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
                                                <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $product->encryptedId() }}">
                                            </div>
                                        </td>                                
                                        <td class="column-image">
                                            <div class="image-50"> <img src="{{$product->imagePath}}"></div>
                                        </td>   
                                        <td class="column-product">
                                            <div class="product-image-container">                                               
                                                <div class="image-description ml-0">
                                                    <a href="{{ route('shop.product', [ $product->encryptedId() ]) }}" class="item-name">{{ $product->name}}</a>
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
                                                        <a href="{{route('admin.products.edit',[$product->encryptedId()])}}">
                                                            <span class="span">
                                                                <i class="fas fa-pen"></i>  
                                                            </span>                                                                           
                                                        </a>
                                                        
                                                    </li> 
                                                    <li>
                                                        <form action="{{ route('admin.products.status.update',[$product->encryptedId() ]) }}" method="post">
                                                            @csrf
                                                            @method('put')
                                                            <button>
                                                                <i class="fas {{ $product->status == 0 ? 'fa-toggle-off' : 'fa-toggle-on'}}"></i>                                                               
                                                            </button>
                                                        </form>  
                                                    </li> 
                                                    @can('delete', $product)
                                                        <li>
                                                            <form action="{{ route('admin.products.destroy',[$product->encryptedId()]) }}" method="post">
                                                                        @csrf
                                                                        @method('delete')
                                                                        <button><i class="far fa-trash-alt"></i></button> 
                                                            </form>
                                                        </li> 
                                                    @endcan                            
                                            
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <td colspan="8" ><label class="text-center">No found Record</label></td> 
                            @endif
                        </tbody>
                    </table>

        
            <div class="mt-2 mb-2 right mr10">
                {{ $products->links() }}
            </div>

            
        
            
      
    </div>


</div>






@endsection