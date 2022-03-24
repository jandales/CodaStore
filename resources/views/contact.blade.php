@extends('layout.front.app')

@section('content')

    
        <div class="content-page">
            <div class="content-image-contact">
                <h1 class="">Contact</h1>
            </div>  
        </div>
    
        
        <div class="container">
           
    
          <div class="row">
            <div class="contact-form">
                <h3>Send us Message</h3>
                <form>
                    <div class="form-block">
                        <label>Email</label>
                        <input type="text">
                    </div>             
    
                    <div class="form-block">
                       
                        <textarea  cols="30" rows="8">How can we Help you!</textarea>
                    </div>
    
                    <div class="form-block">
                      <button class="button">Submit</button>
                    </div>
                    
    
                </form>
            </div>
            <div class="separator"></div>
            
            <div class="contactinfo">
                
               <div class="info">
                <label>Visit us</label>
                <p>St. Lourence Street New York</p>
               </div>
    
               <div class="info">
                <label>Call us</label>
                <p># 098-2323-323</p>
               </div>
    
               <div class="info">
                <label>Email</label>
                <p>Codastore.saleStore.com</p>
               </div>
    
            </div>
          </div>
    
         
        </div>

        @endsection
    
       
        
    
    