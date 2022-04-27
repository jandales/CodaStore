@extends('layout.admin')
@section('content') 
<div class="page-title">
    <h1>Inventory</h1>
</div>  

<div class="panel-table m-t-1">
    <div class="panel-table-header">
        <div class="flex gap20">
            <div class="flex  items-center gap20 w-2">
                <label for="filter">Filter</label>
                <select name="filter" id="filter">   
                    
                    @if($keyword != null)   <option value="">-- Select -- </option>       @endif
                    <option value="all" {{ $filter == 'all' ? 'selected' : ''  }} data-url="{{route('admin.inventory.filter',['all'])}}" >All</option>
                    @foreach ($categories as $category)
                        <option value="{{$category->slug}}" {{ $filter == $category->slug ? 'selected' : ''}}  data-url="{{route('admin.inventory.filter',[$category->slug])}}">{{$category->name}}</option>
                    @endforeach
                </select>
            </div>
             <form id="form-search" class="flex-auto" action="{{ route('admin.inventory.search')}}" method="get"> 
                    <div class="search-input">
                        <span class="icon-right"><i class="fas fa-search"></i></span>
                        <input type="text" id="keyword" name="keyword" placeholder="Search by Name or Sku">
                    </div>
            </form>   
        </div>
    </div>

    
    <table class="table">
        <thead>
            <tr> 
                <th class="column-0"></th>
                <th class="column-image"></th>
                <th class="column-product">Product</th>
                <th class="column-base">Category</th>
                <th class="column-base">Sku</th>
                <th class="column-base">Stock</th>
                <th class="column-base">Action</th>
                <th class="column-base">Update Quantity</th>   
                <th class="column-action"></th>            
            </tr>
        </thead>
        <tbody>
            @if($products->count() == 0)
                <tr class="tr-center">
                    <td colspan="9">No Product Found</td>
                </tr>
            @endif
            @foreach ($products as $product)    
                <tr> 
                    <td class="column-0"></td>      
                    <td class="column-image">
                        <div class="image-50">                     
                            <img src="\{{$product->imagePath}}">                                
                        </div>
                    </td>               
                        <td class="column-product">
                            <div class="product-image-container">
                                
                                <div class="image-description ml-0">
                                    <div class="item-name">{{ $product->name }}</div>
                                </div>                           
                            </div>
                        </td>
                        <td class="column-base">{{ $product->category->name }}</td>
                        <td class="column-base">{{ $product->sku }}</td>
                        <td class="column-base"><span class="stock link-primary">{{ $product->stock->qty }}</span></td>
                        <td class="column-base">
                            <select name="type" class="type">
                                <option value="1">Stock In</option>
                                <option value="0">Stock Out</option>
                            </select>
                        </td>
                        <td class="column-base">
                            <div class="form-group-qty">
                                <div class="btn-num-product-down flex-vert-center btn-add-minus" type="minus"> <i class="fas fa-minus"></i></div>
                                <input class="num-product bg-grey"  item=""  type="number" value="0">
                                <div class="btn-num-product-up flex-vert-center btn-add-minus" type="add"> <i class="fas fa-plus"></i></div>
                            </div>
                        </td>
                        <td class="column-action">
                            <button disabled  data-url = "{{ route('admin.inventory.update.quantity',[$product->stock->id])}}"class="btn btn-primary btn-save">Save</button>
                        </td>
                    </tr> 
            @endforeach
          
        </tbody>
    </table>
</div>
<div class="mt-2 mb-2 right mr10">
    {{ $products->links() }}
</div>
<script>

const btnAddMinusQty = document.querySelectorAll('.btn-add-minus');
const btnsave = document.querySelectorAll('.btn-save');
const numProduct = document.querySelectorAll('.num-product');
let grandParentElement;

if (btnAddMinusQty) {
    btnAddMinusQty.forEach(button => {
        button.onclick = () =>  {
            btnsaveEvent(button);
        }
    })    
}
function btnsaveEvent(button){
    const grandParent = button.closest('tr'); 
    const type = button.getAttribute('type');
    const input = button.parentElement.querySelector('.num-product');
    let qty = parseInt(input.value);              
            
    if (type == 'add') { 
        input.value =  qty += 1;  
    } 
    else {                                
        if (qty == 0) return  input.value =  0;                    
        input.value = qty -= 1;
    }

    const stock = parseInt(grandParent.querySelector('.stock').innerHTML);

    if (qty === 0)            
        buttonState(grandParent, true);
    else             
        buttonState(grandParent, false)   

    // updateStockElement(grandParent, qty)
}

function buttonState(element, state){ 
    const btn =  element.querySelector('.btn-save');    
    if (state == true)
        btn.setAttribute('disabled',state);
    else
        btn.removeAttribute('disabled');
}

function save(url, qty, action){
    $.ajax({
        url : url,
        type : 'POST',
        data : {
            _token : _token,
            _method : _put,
            action : action,
            qty : qty,
        },
        error : errorResponse,
        success : successResponse,
    })
}
function errorResponse(response){}
function successResponse(response){
    const currentstock = grandParentElement.querySelector('.stock');
    const numproduct = grandParentElement.querySelector('.num-product');
    currentstock.innerHTML = response.stock.qty;   
    numproduct.value = 0;
    buttonState(grandParentElement, true);
}
btnsave.forEach(btn => {
    btn.onclick = function(){
        const grandParent = btn.closest('tr');       
        const url = btn.getAttribute('data-url');
        const qty = parseInt(grandParent.querySelector('.num-product').value)
        const selectedType = grandParent.querySelector('.type');
        const type = selectedType.options[selectedType.selectedIndex].value;
        grandParentElement = grandParent;     
        save(url, qty, type);
    }
})

const btnfilter = document.getElementById('filter');
btnfilter.addEventListener('change', function() {
    const url = btnfilter.options[btnfilter.selectedIndex].getAttribute('data-url');
    if(!url) return;
    window.location.href = url;
})



numProduct.forEach(input => {
    input.addEventListener('change', () => {
        let qty = parseInt(input.value);
        const grandParent = input.closest('tr'); 
        let state = false;

        if (qty <= 0) {
            input.value = 0;
            state = true;
        }

        buttonState(grandParent, state);           
        
    })
})
</script>
    



@endsection