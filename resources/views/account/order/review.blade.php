@extends('layout.front.app')
@section('content')
        <div class="container">
            <div class="flex account mt-3 mb-3">                          
                <div class="col1">
                    @include('layout.front.sidebar')

                </div>
                <div class="col2">
                    <div class="card no-border pad-2 bg-grey">
                        <div class="card-heading">
                            <h2>Write a review</h2>                       
                        </div>                       
                       <div class="mt-1 bg-white w-12 p-20">
                                @if (session('error'))
                                    <div class="alert alert-danger">{{ session('error')}}</div>
                                @endif
    
                                @if (session('success'))
                                    <div class="alert alert-success">{{ session('success')}}</div>
                                @endif
    
                              
                                <div class="w-12 p-10">
                                    <label for="">Rate and review purchased product</label>
                                </div>
                                <div class="item-main">
                                    <div class="item-image">                                          
                                        <img src="{{ $product->imagePath }}" alt="">                                   
                                    </div>
                                    <div class="item-name ml-1">
                                        <p>{{ $product->name }}</p>
                                    </div>
                                </div>
    
                                <div class="add-review-wrapper mt-3">               

                                    <div class="mt-2">
                                        <div class="inline">
                                             <h4>Your Rating</h4>
                                           
                                             <ul id="rating" class="rate-stars ml-1" value="{{$review->rating ?? 0}}">
                                                 <li class="rate" rate="1"><i class="far fa-star" aria-hidden="true"></i></li>
                                                 <li class="rate" rate="2"><i class="far fa-star" aria-hidden="true"></i></li>
                                                 <li class="rate" rate="3"><i class="far fa-star" aria-hidden="true"></i></li>
                                                 <li class="rate" rate="4"><i class="far fa-star" aria-hidden="true"></i></li>
                                                 <li class="rate" rate="5"><i class="far fa-star" aria-hidden="true"></i></li>
                                             </ul>                                        
                                        </div>
                                        <div class="mt-1">                    
                
                                                 <form action = "{{route('review.store',[encrypt($product->id)])}}" method="POST">
                                                     @csrf
                                                         <input type="hidden" id="input-rate"  name="rate" value="0">
                                                         <div class="form-block">
                                                             <label for="#">Your Review</label>
                                                             <textarea  name="comments" cols="30" rows="6">{{ $review->comments ?? '' }}</textarea>
                                                         </div> 
                                                         @error('comments')
                                                         <div class="alert alert-danger mb-2">{{ $message }}</div>
                                                         @enderror
                                                     <button  class="btn btn-dark w-2 right">SUBMIT</button>    
                                                 <form> 
                                                  
                
                                         </div>
                                    </div>
                                 </div>
    
                       </div>
    
            
                    </div>  
                </div>
                           
            </div>
        </div>

        <script src="/js/product/ratingevent.js"></script>

        <script>

            document.addEventListener('DOMContentLoaded', function(){

                const productrating = document.getElementById('rating')
                let rating = productrating.getAttribute('value');
                const rate = document.querySelectorAll('.rate');

                document.querySelector('[name="rate"]').value = rating   

                rate.forEach(elem => {
                     if(elem.getAttribute('rate') <= rating)                      
                     {
                        elem.firstElementChild.classList.replace('far' , 'fas');
                        return
                     }
                })
            })

        </script>
      
@endsection