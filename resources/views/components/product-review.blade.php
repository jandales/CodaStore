<div class="review-wrapper">
    <div class="review-list">
        <ul>
            @auth
            <li>
                @php                  
                    $userReview =  auth()->user()->review($product);               
                    if($userReview == null)
                        $reviews = $product->reviews; 
                    else 
                        $reviews = $product->reviews->except($userReview->id); 
                @endphp


                 @if ($userReview != null)
                    <li>
                        <div class="comment">
                            <div class="comment-avatar">                                                      
                                 <img src="{{ auth()->user()->imagePath }}" alt="">                                                                                                     
                            </div>
                            <div class="comment-message">
                                <div class="comment-user-rating">
                                    <div class="comment-user">
                                            <p>{{ auth()->user()->name }}</p>
                                    </div>                                                        
                                    <div class="commnet-rating">
                                        <x-rating :rating="$userReview->rating" /> 
                                    </div>
                                    
                                </div>
                                    <p class="mt-1">{{ $userReview->comments }}</p>
                                    <div class="comment-action mt-1">
                                        <ul>
                                            <li><span class="edit-review" value="{{ $userReview->comments }}" rate="{{ $userReview->rating }}"><i class="far fa-edit"></i></span></li>
                                            <li>
                                                <form id="form-review-delete" action="{{ route('review.destroy',[ $userReview->encryptedId() ])}}" method="POST">
                                                    @csrf
                                                    @method('delete')  
                                                <button class="btn-link">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>                                                
                                                </form>
                                                
                                            </li>
                                        </ul>
                                    </div> 
                            </div>
                           
                        </div>
                    </li>
                @endif
            </li>

            @endauth                               

            @guest
                    @php
                        $reviews = $product->reviews;     
                    @endphp
            @endguest

           @foreach ($reviews as $review)
                <li>
                    <div class="comment">
                        <div class="comment-avatar">                                                   
                             <img src="{{ $review->user->imagePath }}" alt="">   
                        </div>
                        <div class="comment-message">
                            <div class="comment-user-rating">
                                <div class="comment-user">
                                        <p>{{ $review->user->name }}</p>
                                </div>                                                    
                                <div class="commnet-rating">
                                        <ul rate-star="{{ $review->rating }}" class="ul-rate-star rate-stars"></ul>
                                </div>                                                        
                            </div>
                            <p>{{ $review->comments }}</p>
                        
                        </div>
                       
                    </div>
                </li>                                    
           @endforeach
               
           
            


        </ul>
    </div>

    <div class="add-review-wrapper">
       <div>
            <h2>Add Review</h2>
            <p class="mt">Your email address will not be published.</p>
       </div>

       <div class="mt-2">
           <div class="inline">
                <h4>Your Rating</h4>
                <ul id="rating" class="rate-stars ml-1">
                    <li class="rate" rate="1"><i class="far fa-star" aria-hidden="true"></i></li>
                    <li class="rate" rate="2"><i class="far fa-star" aria-hidden="true"></i></li>
                    <li class="rate" rate="3"><i class="far fa-star" aria-hidden="true"></i></li>
                    <li class="rate" rate="4"><i class="far fa-star" aria-hidden="true"></i></li>
                    <li class="rate" rate="5"><i class="far fa-star" aria-hidden="true"></i></li>
                </ul>                                        
           </div>
           <div class="mt-1">

                        <form  action = "{{route('review.store',[$product->encryptedId() ])}}" method="POST">
                            @csrf
                                <input type="hidden" id="input-rate"  name="rate" value="0" hidden>
                                <div class="form-block">
                                    <label for="#">Your Review</label>
                                    <textarea id="text-editor"  name="comments"  cols="30" rows="6"></textarea>
                                </div> 
                            <button class="btn btn-dark">SUBMIT</button>    
                        <form>  
                            
            </div>
       </div>
    </div>
</div>