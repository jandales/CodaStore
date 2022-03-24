@extends('layout.admin')
@section('content') 
<div class="page-title">
    <h1>Inventory</h1>
</div>  

<div class="panel-table m-t-1">
    <div class="panel-table-header">
        <div class="search-input">
            <span class="icon-right"><i class="fas fa-search"></i></span>
            <input type="text" placeholder="Search here">
        </div>
      
    </div>

    
    <table class="table">
        <thead>
            <tr>
                <th class="colunm-1">
                    <div class="checkbox">
                        <input type="checkbox" name="id" id="">
                    </div>
                </th>
                <th class="colunm-2">Product</th>
                <th>Sku</th>
                <th>Quantity</th>
                <th>Update Quantity</th>               
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < 10; $i++)
            <tr>
                <td class="column-1">
                    <div class="checkbox">
                        <input type="checkbox" name="id" id="">
                    </div>
                </td>
                <td class="column-2">
                    <div class="product-image-container">
                        <div class="image">                     
                            <img src="\img\products\classic-trench-coat-20221643617007-1.webp">                                
                        </div>
                        <div class="image-description">
                            <div class="item-name">classic-trench-coat</div>
                        </div>                           
                    </div>
                </td>
                <td>
                    bl432-129
                </td>
                <td>
                   12
                </td>
                <td class="column-5">
                    <div class="form-group-qty center">
                        <div class="btn-num-product-down flex-vert-center add-minus-quantity" type="minus"> <i class="fas fa-minus"></i></div>
                        <input class="cart-qty num-product bg-grey"  item=""  type="number" value="0">
                        <div class="btn-num-product-up flex-vert-center add-minus-quantity" type="add"> <i class="fas fa-plus"></i></div>
                    </div>
                </td>
            </tr>
            @endfor
          
        </tbody>
    </table>
</div>


    



@endsection