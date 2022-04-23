@extends('layout.admin')

@section('content') 


<div class="page-title">
    <h1>Reviews</h1>
   
</div>

@if(session('success'))
<br>
    <div class="alert alert-success">{{ session('success') }}</div>    
@endif
   
<div class="row" >
    <div class="panel-table m-t-2 w-12">        
        <div class="toolbar justify-content-space-between action-toolbar hidden"> 
            <label class="title selected-items">2 item Selected</label>
            <div class="btn-action">        
                <span id="deleteSelected" link = {{ route('admin.users.destroySelectedItem') }} class="btn btn-light"><i class="fas fa-trash"></i></span>
                <span  onclick="clearSelection()" class="btn btn-light"><i class="fas fa-times"></i></span> 
            </div>
        </div>          
        <div class="toolbar justify-content-space-between  default-toolbar"> 
            <form id="formSearch" action="{{ route('admin.users.search') }}" method="post">
                @csrf
                <div class="search-input"> 
                    <span class="icon-left"></span>                           
                    <input type="text" placeholder="Search" name="search">
                    <span class="icon-right" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>
                </div>                     
            </form>
        </div>
        
            <table class="table">
                <thead>
                    <tr>
                        <th>                   
                             <div class="checkbox">
                                <input type="checkbox" id="parentCheckbox" name="checkbox" >                             
                             </div>
                         </th>
                        <th>User</th>
                        <th>Product</th> 
                        <th>Rating</th>                                         
                        <th>Comment</th>                      
                        <th>Date Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <form id="form"  method="post">
                        @csrf 
                        @method('delete')
                @if ( count($reviews) != 0 )

                @foreach ($reviews as $review) 
                    <tr>
                        <td>                         
                            <div class="checkbox">
                                    <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $review->id }}">                            
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap10">
                                <div class="avatar-sm">
                                    <img src="{{ $review->user->avatar() }}" alt="" srcset="">
                                </div>
                                <span>{{ $review->user->name }}</span>
                            </div>
                        </td>                        
                        <td>
                            <div class="flex justify-content-start align-items-flex-start">
                                <div class="image">
                                    <img src="/{{ $review->product->imagePath}}" alt="" srcset="">
                                    </div>
                                    <p class="ml-1">{{ $review->product->name }}</p>
                                </div>
                        </td> 
                        <td><span class="star">{{ $review->rating }} <i class="fas fa-star"></i></span></td> 
                        <td class="text-align-left">
                            <div class="inline justify-content-between ">
                                <p>{{ substr($review->comments, 0, 50) . '....' }}</p>                               
                            </div>
                        </td>                      
                        <td class="text-align-left"><p>{{ $review->createdAtDate()}}</p></td>
                      
                        <td width="100px"> 
                            <div class="table-action">
                                <ul>                                     
                                    <li>                          
                                        <a data-modal-target="read-review" modal-data="{{ json_encode($review) }}" href="#">
                                            <span class="span">
                                                <i class="fas fa-eye"></i>  
                                            </span>                                                                           
                                        </a>
                                    </li>    
                                    <li>
                                        <a href="#"  onclick="destroy('/admin/reviews/block/{{$review->id}}')">
                                            <span class="span">
                                                <i class="fas fa-trash"></i>  
                                            </span>                                                                           
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
      
              
                @endforeach

                    @else
                    <tr><td>No found Record</td></tr>
                    @endif
  

                  
                  
                </form>
                </tbody>
            </table>
        
        
            <form id='destroy'  method="post">
                @csrf      
                @method('put')  
            </form>

            <div class="mt-2 mb-2 right mr10">
                {{ $reviews->links() }}
            </div>
        
            
        
    </div>

   
</div>


<div id="read-review"  class="modal">
    <div class="modal-content">    
        <div class="modal-header">
            <h4>Review</h4> 
            <span  class="modal-close">&times</span>
        </div>  
        
      <div class="m-t-2">
          <div class="form-block">
              <label for="">User</label>
              <input type="text"  id="user" name="name" value="jesus Andales" disabled>
          </div>
          <div class="form-block">
            <label for="">Product</label>
            <input type="text" name="name" id="product" value="jesus Andales" disabled>
         </div>

         <div class="form-block">
            <label for="">Comment</label>
            <textarea name="" id="comment" cols="30" rows="10" disabled></textarea>
         </div>

         <div>
      
             <button  id="modal-button"  class="btn btn-primary">block</button>    
                
         </div>

      </div>        
    </div>
</div>


<script>


const modaltrigger = document.querySelectorAll('[data-modal-target]');


modaltrigger.forEach(modal => {

    modal.addEventListener('click', function(){

        const array = modal.getAttribute('modal-data');       
        let data = JSON.parse(array);


        document.getElementById('user').value = data.user.name;
        document.getElementById('product').value = data.product.name;
        document.getElementById('comment').value = data.comments;

        const button = document.getElementById('modal-button')

        button.addEventListener('click', function(){
            destroy('/admin/reviews/block/'+ data.id);
        })

        if(data.block == 1){
            button.innerText = 'Unblock';
            button.classList.replace("btn-danger", "btn-primary")
            return;
        }
      
            button.innerText = 'Block';
            button.classList.replace("btn-primary", "btn-danger")
    })
})












</script>


@endsection