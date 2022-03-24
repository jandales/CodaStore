@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Products</h1>
</div>
<div class="row" >
    <div class="panel m-t-2 w-12">
        <div class="panel-header">            
        </div>
        <div class="panel-body">        
                <div class="toolbar justify-content-space-between action-toolbar hidden"> 
                    <label class="title selected-items">2 item Selected</label>
                    <div class="btn-action">                           
                        <span onclick="url('post','/admin/products/delete')" class="btn btn-light"><i class="fas fa-trash"></i></span>
                        <span onclick="clearSelection()" class="btn btn-light"><i class="fas fa-times"></i></span> 
                    </div>
                </div> 
                <div class="toolbar default-toolbar"> 
                        <form id="formSearch" action="{{ route('products.search') }}" method="post">
                            @csrf
                            <div class="search-input"> 
                                <span class="icon-left" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>                                                         
                                <input type="text" placeholder="Search" name="search" value="{{ $search }}">
                                <a href="{{ route('products')}}">
                                    <span class="icon-right"><i class="fas fa-times"></i></span>
                                </a>
                            </div>                     
                        </form>
                       

                        <a href="/admin/products/add" class="btn btn-primary mr-2">
                            <span><i class="fas fa-plus-circle"></i></span>
                             Add item
                        </a>
                </div>  
                <table class="table"> 
                         
                <thead>
                    <tr>
                        <th>                   
                             <div class="checkbox">
                                <input type="checkbox" id="parentCheckbox" name="checkbox" >
                             </div>
                         </th>
                        <th>Product Name</th>
                        <th>Collection</th>
                        <th>Categories</th>                                          
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Date Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @if ( count($products) != 0 )

                @foreach ($products as $product)                   
                
                <tr>
                    <td>
                        <div class="checkbox">
                            <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $product->id }}">
                        </div>
                    </td>
                    <td>
                        <div class="product-image-container">
                            <a  href="{{route('product.download',[$product])}}" class="image">                     
                                <img src="/{{$product->imagePath}}">                                
                            </a>
                            <div class="image-description">
                                <a href="{{ route('shop.product', [$product ]) }}" class="item-name">{{ $product->name}}</a>
                            </div>                           
                        </div>
                    </td>                        
                    <td><p>{{ $product->collection() }}</p></td> 
                    <td><p>{{ $product->category->name }}</p></td>
                    <td><p>{{ $product->stock->qty }}</p></td>
                    <td><p>@money($product->price)</p></td> 
                    <td><p>{{ $product->created_at }}</p></td>
                    <td> 
                        <div class="table-action">
                            <ul>   
                                <li>                                   
                                    <a href="{{route('product.edit',[$product])}}">
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
                                            <a href="{{ route('stock.edit',[$product->stock]) }}">
                                                <i class="far fa-list-alt"></i>
                                                <span class="ml-1">Inventory</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('price.edit',[$product]) }}">
                                                <i class="fas fa-tag"></i>
                                                <span class="ml-1">Pricing</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('product.variants',[$product]) }}">
                                                <i class="far fa-file-alt"></i>
                                                <span class="ml-1">Variant</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a  onclick="urldelete('/admin/products/destroy/{{$product->id}}')">
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
        
        
            <form  id="deleteform" method="POST">
                @csrf
                @method('delete')
            </form>
        
            
        </div>
    </div>

   
</div>

<script src="/js/admin/checkbox.js"></script>
<script>    
     
     checkBoxProperties.toolbar = true
     ajax()

function url(method, route){
    let form =  document.getElementById('form')
    form.setAttribute('action', route)
    form.setAttribute('method', method)
    form.submit() 
}

function urldelete(route){
    let form =  document.getElementById('deleteform')
    form.setAttribute('action', route)
    form.submit()  
}

function ajax(){
    $.ajax({
        url : '/admin/products/all',
        type : 'get',
        success : function(response){
            console.log(response)
        }
    })
}

</script>



@endsection