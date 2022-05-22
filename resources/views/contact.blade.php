@extends('layout.front.app')

@section('content')

    
        <div class="content-page">
            <div class="content-image-contact">
                <h1 class="">Contact</h1>
            </div>  
        </div>
    
        
        <div class="container">              
          <div class="contact-wrapper mt-2 mb-2">
            <div class="contact-form">
              @if (Session('success'))
                <div class="alert alert-success">{{ Session('success') }}</div>
              @endif              
                <h3 class="text-center">Send us Message</h3>
                <form action="{{ route('sendMessage') }}" method="POST">
                  @csrf
                    <div class="form-block">
                        <label>Email</label>
                        <input type="email" name="email">
                        @error('email')
                          <small class="error-message">{{ $message }}</small>    
                        @enderror
                    </div>             
    
                    <div class="form-block">                       
                        <textarea name="content"  cols="30" rows="8" placeholder="How can we Help you!"></textarea>
                        @error('content')
                          <small class="error-message">{{ $message }}</small>    
                        @enderror
                    </div>
    
                    <div class="form-block">
                      <button class="btn btn-dark">Send</button>
                    </div>                   
    
                </form>
            </div> 
            {{-- <div class="contactinfo">
               
              <div class="mt-2">
                <div class="info">
                  <h3>Visit us</h3>
                  <p>St. Lourence Street New York</p>
                 </div>
      
                 <div class="info">
                  <h3>Call us</h3>
                  <p># 098-2323-323</p>
                 </div>
      
                 <div class="info">
                  <h3>Email us</h3>
                  <p>Codastore.saleStore.com</p>
                 </div>
              </div>
    
            </div> --}}
          </div>
    
         
        </div>

        @endsection
    
       
        
    
    