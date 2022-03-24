@extends('layout.admin')

@section('content') 

<div class="page-title">
    <h1>Coupon</h1>
    <a href="{{route('admin.coupons') }}"   class="btn btn-danger">Cancel</a> 
</div>
     

    <form id= "form" action="{{ route('admin.coupon.store')}}" method="post">
        @csrf  
        <input type="hidden" id="productlist" name="products" value="">
    <div class="panel m-t-2 w-12">
        <div class="panel-heading">           
            <label class="panel-title">Create Coupon</label>          
        </div>
        @if (session('error'))
        <div class="alert alert-danger">{{session('error')}}</div>
      @endif
        <div class="panel-body">              
              
                    <div class="form-block">
                        <label for="email">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}">                 
                    </div>
                    @error('name')
                        <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                    @enderror  

                    <div class="form-block">
                        <label for="email">Description</label>
                        <input type="text" name="description" value="{{ old('description')}}">                 
                    </div>

                    <div class="form-block">
                        <label for="">Discount type</label>
                        <select name="discount_type">
                            <option value="">Choose....</option>
                            <option value="0" @if (old('discount_type') == 0) selected @endif>Percentage discount</option>
                            <option value="1" @if (old('discount_type') == 1) selected @endif>Fixed cart discount</option>
                            <option value="2" @if (old('discount_type') == 2) selected @endif>Fixed product discount</option>
                        </select>    
                        
                        @error('discount_type')
                            <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                        @enderror  

                    </div>
                    <div class="form-block">
                        <label for="email">Coupon amount</label>
                        <input type="text" name="amount" value="{{ old('amount') }}">                 
                    </div>
                    @error('amount')
                     <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                     @enderror 
                    <div class="form-block">
                        <label for="email">Date expitation</label>
                        <input type="date" id="birthday" name="expire_at" value="{{ old('expire_at') }}">        
                    </div>
                    @error('expire_at')
                        <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                    @enderror 
        </div>
    </div>

                 
                    <div class="tabs  m-t-2">
                        <div class="tab-header">
                                    <ul class="tab-nav">                                       
                                        <li><a href="#"  class="tabs-button" data-for-tab="2" >Usage Ristriction</a></li>
                                        <li><a href="#"  class="tabs-button" data-for-tab="3" >Usage Limit</a></li>
                                    </ul>
                        </div>                           

                        <div class="tabs-content" data-tab= "2">                                
                                <div class="form-block">
                                    <label for="email">Minimun spend amount</label>
                                    <input type="text" name="min_amount" value="{{ old('min_amount') }}">                 
                                </div>
                                @error('min_amount')
                                    <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                                @enderror 
                    
                                <div class="form-block">
                                    <label for="email">Maximun spend amount</label>
                                    <input type="text" name="max_amount" value="{{ old('max_amount') }}">                 
                                </div>
                                @error('max_amount')
                                    <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                                @enderror 
                    
                                {{-- <div class="form-block">
                                    <label for="email">Product</label>
                                    <input type="text" class="product_search" data-type="include" name="include_product_id" value="" placeholder="Search here">                 
                                </div> --}}

                                <div class="form-block">
                                    <label for="email">Product</label>

                                    <ul class="product-list" data-type = "include">                                      
                                      
                                    </ul>
                                    
                                    <div class="dropdown-wrapper">                                       
                                        <div class="dropdown-input-wrapper ">                                         
                                            <input type="text"  class="product_search"  data-type="include"  name="include_product_id"  id="dropdown-input" value=""> 
                                        </div>
                                        <div class="dropdown-list-wrapper">
                                            <ul class="dropdown-ul-list include-product">
                                              
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                @error('include_product_id')
                                     <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                                @enderror 
                    
                                {{-- <div class="form-block">
                                    <label for="email">Product excluded</label>
                                    <input type="text" name="exclude_product_id" class="product_search"  data-type="exclude" value="" placeholder="Search here">                 
                                </div> --}}

                                <div class="form-block">
                                    <label for="email">Exclude Product</label>

                                    <ul class="product-list" data-type = "exclude">
                                                                               
                                    </ul>
                                    
                                    <div class="dropdown-wrapper">                                       
                                        <div class="dropdown-input-wrapper ">                                         
                                            <input type="text"  class="product_search" data-type = "exclude" name="include_product_id"  value=""> 
                                        </div>
                                        <div class="dropdown-list-wrapper">
                                            <ul  class="dropdown-ul-list exclude-product">
                                              
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                
                                @error('exclude_product_id')
                                     <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                                @enderror 

                         </div>



                        <div class="tabs-content" data-tab= "3">
                                <div class="form-block">
                                    <label for="email">Limit per coupon</label>
                                    <input type="number" name="limit_per_coupon" value="">                 
                                </div>
                                @error('limit_per_coupon')
                                    <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                                @enderror 
                                <div class="form-block">
                                    <label for="email">Limit usage to included item</label>
                                    <input type="number" name="limit_to_xitems" value="">                 
                                </div>
                                @error('limit_to_xitems')
                                    <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                                 @enderror 
                                <div class="form-block">
                                    <label for="email">Limit per user</label>
                                    <input type="number" name="limit_per_user" value="">                 
                                </div>
                                @error('limit_per_user')
                                    <div class="alert alert-danger m-t-1">{{ $message }}</div>                
                                @enderror 
                        </div>                                
                    </div>
    

    <div class="m-t-2">
        <button id="save"  class="btn btn-primary right">Save</button>
    </div>

</form>

<script src="/js/admin/coupon.js"></script>
<script> 
document.addEventListener('DOMContentLoaded', function(){
    const products = JSON.parse(@json(old('products')));
    if(products){        
       populateList(products)
       populateElementDOM();
       Showlist()
   }
  
  
})


   

</script>


@endsection