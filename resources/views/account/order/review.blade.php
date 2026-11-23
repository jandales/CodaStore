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
    
                                @if (session('review_success'))
                                    <div class="review-note review-note--success" role="status">
                                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                                        <span>{{ session('review_success') }}</span>
                                    </div>
                                @endif
    
                              
                                <div class="w-12 p-10">
                                    <label for="">Rate and review your purchase</label>
                                </div>
                                <div class="item-main">
                                    <div class="item-image">                                          
                                        <img src="{{ $product->imageUrl() }}" alt="">                                   
                                    </div>
                                    <div class="item-name ml-1">
                                        <p>{{ $product->name }}</p>
                                    </div>
                                </div>
    
                                <div class="add-review-wrapper mt-3">               

                                    <div class="mt-2">
                                        <div class="inline">
                                             <h4>Your rating</h4>
                                           
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
                                                         <input type="hidden" id="input-rate"  name="rate" value="{{ old('rate', $review->rating ?? 0) }}">
                                                         @error('rate')
                                                         <small class="error-message" role="alert">{{ $message }}</small>
                                                         @enderror
                                                         <div class="form-block">
                                                             <label for="review-comments">Your review</label>
                                                             <textarea id="review-comments" name="comments" rows="6" maxlength="1000">{{ old('comments', $review->comments ?? '') }}</textarea>
                                                         </div>
                                                         @error('comments')
                                                         <small class="error-message" role="alert">{{ $message }}</small>
                                                         @enderror
                                                     <button class="btn btn-dark">{{ isset($review) && $review ? 'Update review' : 'Post review' }}</button>
                                                 </form>
                                                  
                
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
                // Prefer the value kept after a validation error, else the saved rating.
                let rating = parseInt(document.querySelector('[name="rate"]').value, 10) || productrating.getAttribute('value');
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