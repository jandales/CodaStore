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
                <span id="destroy-selected-review" data-url = {{ route('admin.reviews.selected.destroy') }} class="btn btn-light"><i class="fas fa-trash"></i></span>
                <span id="clear-selection"  class="btn btn-light"><i class="fas fa-times"></i></span> 
            </div>
        </div>          
        <div class="toolbar justify-content-space-between  default-toolbar"> 
            <form id="formSearch" action="{{ route('admin.reviews.search') }}" method="get">               
                <div class="search-input"> 
                    <span class="icon-left" onclick="document.getElementById('formSearch').submit()"><i class="fas fa-search"></i></span>                           
                    <input type="text" placeholder="Search" name="keyword" value="{{ $keyword ?? ''}}">
                    <a href="{{ route('admin.reviews') }}" class="{{ $keyword ?? 'hidden'}}"><span class="icon-right"><i class="fa fa-times"></i></span></a>
                </div>                     
            </form>
        </div>
        
            <table class="table">
                <thead>
                    <tr>
                        <th class="column-1">                   
                             <div class="checkbox">
                                <input type="checkbox" id="parentCheckbox" name="checkbox" >                             
                             </div>
                         </th>
                        <th class="column-3">User</th>
                        <th>Product</th> 
                        <th class="column-xs">Rating</th>                                         
                        <th>Comment</th>                      
                        <th class="column-base">Date Created</th>
                        <th class="column-action"></th>
                    </tr>
                </thead>
                <tbody>
                    <form id="destroy-reviews"  method="post">
                        @csrf 
                        @method('delete')
                        @if ( count($reviews) == 0 )
                            <tr> <td colspan="7" ><label class="text-center">No found Record</label></td> </tr>
                        @endif
                        @foreach ($reviews as $review) 
                            <tr>
                                <td class="column-1">                         
                                    <div class="checkbox">
                                            <input type="checkbox" class="childCheckbox" name="selected[]"  value="{{ $review->encryptedId() }}">                            
                                    </div>
                                </td>
                                <td class="column-3">
                                    <div class="flex items-center gap10">
                                        <div class="avatar-sm">
                                            <img src="{{ $review->user->avatar() }}" alt="" srcset="">
                                        </div>
                                        <span>{{ $review->user->name }}</span>
                                    </div>
                                </td>                        
                                <td>
                                    <div class="flex justify-content-start align-items-flex-start">
                                        <div class="image-50">
                                            <img src="{{ $review->product->imagePath}}" alt="" srcset="">
                                            </div>
                                            <p class="ml-1">{{ $review->product->name }}</p>
                                        </div>
                                </td> 
                                <td class="column-xs"><span class="star">{{ $review->rating }} <i class="fas fa-star"></i></span></td> 
                                <td class="text-align-left">
                                    <div class="inline justify-content-between ">
                                        <p>{{ substr($review->comments, 0, 50) . '....' }}</p>                               
                                    </div>
                                </td>                      
                                <td class="column-base text-align-left"><p>{{ $review->createdAtDate()}}</p></td>
                            
                                <td class="column-action"> 
                                    <div class="table-action">
                                        <ul>                                     
                                            <li>                      
                                                <a class="read-review"  modal-data="{{ json_encode($review) }}">
                                                    <span class="span">
                                                        <i class="fas fa-eye"></i>  
                                                    </span>                                                                           
                                                </a>
                                            </li>    
                                            <li>                                            
                                                <span class="span destroy-review" data-url={{ route('admin.reviews.destroy',[$review->encryptedId()])}}>
                                                     <i class="fas fa-trash"></i>  
                                                </span>                                                                           
                                                
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
            
                    
                        @endforeach
                    </form>
                </tbody>
            </table>  
            <div class="mt-2 mb-2 right mr10">
                {{ $reviews->links() }}
            </div>
    </div>
</div>


<div id="modal-read-review"  class="modal">
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





@endsection