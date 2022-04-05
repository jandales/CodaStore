@extends('layout.front.app')
@section('content') 
    <div class="container">
        <div class="flex account mt-3  mb-3">          
            <div class="col1">
                    @include('layout.front.sidebar')
            </div>
            <div class="col2">
                <div class="card no-border pad-2 bg-grey">
                    <div class="card-heading">
                        <h2>Wishlist</h2>                           
                    </div>
                    @if (session('success'))
                        <div class="alert alert-success alert-bordered mt-1 mb-1">{{ session('success')}}</div>
                    @endif    
              
                        <div class="table-responsive">
                            <table class="table bordered wishlist-table bg-white vertical-top mt20">
                                <thead>
                                    <tr class="tr-action bg-white hidden">
                                        <th colspan="5">
                                           <div class="flex space-between align-items-center">
                                              <label for="" class="txt-sm cgray-dark"><span class="txt-sm item-selected">2</span> items selected</label>

                                              <ul class="flex flex-end gap5 wishlist-action-wrapper mr-1">                                              
                                               
                                                <li>
                                                    <span  class="tbl-action txt-r" onclick="submitDeleteAll()">
                                                        <i class="far fa-trash-alt"></i>
                                                    </span>
                                                    <form id="form-delete-all"  action="{{ route('wishlist.destroy.all')}}" method="POST">
                                                        @csrf
                                                        @method('delete')  
                                                        <input type="hidden" name="items">                                                    
                                                    </form> 
                                                </li>
                                                <li>
                                                    <span  class="tbl-action txt-r cancel">
                                                        <i class="fas fa-times"></i>
                                                    </span>    
                                                </li>                                                
                                            </ul> 
                                               
                                           </div>
                                        </th>                                 
                                   </tr>
                                    <tr>
                                        <th class="tblCheckbox"><input type="checkbox" name="" id="parentCheckbox"></th>
                                        <th class="padding-left-0">PRODUCT</th> 
                                        <th class="text-centered">QTY</th>                                   
                                        <th class="text-centered">Price</th>                                        
                                        <th class="text-centered">ACTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    @forelse  ($wishlists as $wishlist)  
                                        <tr>
                                            <td class="tblCheckbox"><input type="checkbox" class="childCheckbox" name="" id="{{$wishlist->id}}"></td>
                                            <td class="padding-left-0">
                                                <div class="flex justify-content-flex-start">
                                                    <div class="image120">
                                                        <img class="img" src="/{{ $wishlist->product->imagePath }}" alt="">  
                                                    </div>
                                                    <div class="product-name ml-1">
                                                        <p>{{ $wishlist->product->name }}</p>      
                                                    </div>
                                                </div>
                                            </td> 
                                            <td class="vertical-top text-centered"><p class="m-t-10">QTY: {{$wishlist->qty}}</p></td>
                                            <td class="vertical-top text-centered"><p class="m-t-10">@money($wishlist->product->regular_price)</p></td>
                                                                    
                                            <td  class="w-100 vertical-top">
                                                <ul class="flex flex-end gap5 wishlist-action-wrapper">
                                                    <li>
                                                        <span class="tbl-action add-to-cart" data-modal-target="modal-update-product" data-id="{{ $wishlist->id }}">
                                                            <i class="fas fa-cart-plus"></i>
                                                        </span>
                                                    </li>
                                                   
                                                    <li>
                                                       <span  class="tbl-action"  onclick="submitDelete('form-delete','{{ route('wishlist.destroy',[ $wishlist ])}}')">
                                                       
                                                            <i class="far fa-trash-alt"></i>
                                                        </span>
                                                        <form id="form-delete"  method="POST">
                                                            @csrf  @method('delete')                         
                                                        </form> 
                                                    </li>
                                                </ul> 
                                                   
                                                </div> 
                                                                                     

                                            </td>                                      
                                        </tr>
                                    @empty
                                        <tr><td colspan="3">No Item found</td></tr>
                                    @endforelse                             
                                </tbody>
                            </table>                  
                     
                    </div>                    
                </div>
            </div>              
         </div>
    </div>

    
   
   <div id="modal-update-product" class="modal">                                                        
        <div class="modal-content">
            <div class="modal-heading">
                <h1>Product Overview</h1>
                <span  class="modal-close">&times</span>                   
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
  
    <script>


        const parentCheckbox = document.getElementById('parentCheckbox');
        const table = document.querySelector('table')
        const childCheckbox =  document.querySelectorAll('.childCheckbox')
        let selectedItemcount = table.querySelector(".item-selected")
        let cancelAction = table.querySelector(".cancel")    

        function submitDeleteAll(){
            event.preventDefault(); 
            wishlists = [];
            const form = document.getElementById('form-delete-all');           
            childCheckbox.forEach(checkbox => {
                if(checkbox.checked) wishlists.push(checkbox.getAttribute('id'))           
            })          
            let input = document.querySelector('input[name="items"]')
            input.value = JSON.stringify(wishlists)           
            form.submit()
        }

        function submitDelete(formId, url){
            const form = document.getElementById(formId)                 
            form.setAttribute("action",url)
            form.submit()
        }
        
        let addtocart = document.querySelectorAll('.add-to-cart')

        addtocart.forEach(elem => {
            elem.addEventListener('click', function(){
                  let product = elem.getAttribute('data-id')                  
                    update(product)
             })
        })    
        function update(product){
            let modal  = document.getElementById('modal-update-product')
            body = modal.querySelector('.modal-body')        
            body.innerHTML = "";
            $.ajax({
                url :'/wishlist/addtoCart/'+product,
                type : 'GET',
                success : function(response){ 
                    body.innerHTML = response.view
                    getVariants(response.id)
                }
            })
        }       
      

        function submitToCart(formId){
            event.preventDefault()
            const form = document.getElementById(formId)                   
            let result =  validateProperties()

            if(result[0].status === true) return  alertMessage(result['0'].message)                      
         
            setProperties();
            form.submit()
        }

       
        function setProperties()
        {
            let inputProperties = document.querySelector('.properties') 
            inputProperties.value = JSON.stringify(properties)  
        }

     


 

     




        
    
</script>


@endsection