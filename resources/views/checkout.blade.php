<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apprael Store</title>  
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">  
    <meta name="csrf-token" content="{{ csrf_token() }}">    
    
</head>

<body>
    <div class="container-lg mx-auto">
        <a href="{{route('home')}}"><h1 class="block text-center mt-4 md-mt-2 text-2xl">CodaStore</h1></a>      
        <div class="step-progress-wrapper w-6 sm-w-12">
            <div class="step-circles">
                <div class="step {{ checkoutProgress() == 0 || checkoutProgress() == 30 ||  checkoutProgress() == 60 ||  checkoutProgress() == 100 ? 'active' : '' }}">
                    <div class="circle"><i class="fas fa-info"></i></div>
                    <strong>Information</strong>
                </div>

                <div class="step {{ checkoutProgress() == 30  || checkoutProgress() == 60 || checkoutProgress() == 100   ? 'active' : '' }}">
                    <div class="circle"><i class="fas fa-shipping-fast"></i></div>
                    <strong>Shipping</strong>
                </div>

                <div class="step {{ checkoutProgress() == 60  ||  checkoutProgress() == 100 ? 'active' : '' }}">
                    <div class="circle"><i class="fas fa-credit-card"></i></div>
                    <strong>Payment</strong>
                </div>  
                
                <div class="step {{ checkoutProgress() == 100  ? 'active' : '' }}">
                    <div class="circle"><i class="fas fa-credit-card"></i></div>
                    <strong>Completed</strong>
                </div>   
            </div>
            <div style="--width:{{ checkoutProgress() }};" class="progress-bar"></div>       
        </div>
        @if (!(request()->is('/checkout/completed')))
            <div class="sm-visible mt-5">
                <span id="open-cart-summary" class="sm-text-right change">
                    <i class="fa fa-bag"></i>
                    Open Cart
                </span>
            </div>
        @endif
        @yield('content')
    </div>

    <div class="modal" id="shipping-address-modal">
        <div class="modal-sidebar">
            <div class="sidebar-close">                    
                <span class="close-drawer"><i class="fas fa-times"></i></span>
            </div>         
            <div class="sidebar-heading">
                <p>Shipping Address</p>
            </div>
            <div class="sidebar-body">
                <form id="form-shipping-address" method="post">
                    @csrf          
                    @method('PUT')    
                    @foreach (shippingAddress() as $item)        
                            <div class="address-item {{$item->status == 1 ? 'active' : ''}}">
                                <input type="radio" name="selected"  class="shipping-address-input"  value="{{route('account.shippingaddress.update-status',[ $item->id ])}}" {{$item->status == 1 ? 'checked' : ''}}>
                                <ul>
                                    <li>{{ $item->fullName() }}</li>
                                    <li>{{ $item->fullAddress() }}</li>
                                </ul>
                            </div>                 
                    @endforeach
               <button id="confirm-shipping-address" class="btn btn-success w-12" >CONFIRM</button>
            </form>
            </div>
           
            <div class="sidebar-footer">
                
            </div>
        </div> 
    </div>
    <div class="modal" id="payment-option-modal">
        <div class="modal-sidebar">
            <div class="sidebar-close">                    
                <span class="close-drawer"><i class="fas fa-times"></i></span>
            </div>         
            <div class="sidebar-heading">
                <p>Shipping Address</p>
            </div>
            <div class="sidebar-body">
                <form id="form-payment-option" method="post">
                    @csrf          
                    @method('PUT')    
                    @foreach (paymentOptions() as $item)        
                            <div class="payment-option-item {{$item->status == 1 ? 'active' : ''}}">
                                <input type="radio" name="selected"  class="payment-option-input"  value="{{route('account.payment-option.update.status',[ $item->id ])}}" {{$item->status == 1 ? 'checked' : ''}}>
                                <ul>
                                    <li>{{ $item->card_name }}</li>
                                    <li>{{ $item->card_number }}</li>
                                </ul>
                            </div>                 
                    @endforeach
                    <button id="confirm-payment-option" class="btn btn-success w-12" >CONFIRM</button>
                </form>
            </div>
           
            <div class="sidebar-footer">
                
            </div>
        </div> 
    </div>

    <div class="spinner-wrapper">
        <div class="spinner-container">
           <div class="spinner"></div>
           <div class="loading-success">
               <span><i class="fa fa-check"></i></span>
               <a href="#" id="btn-payment-completed">OK</a>
           </div>
           {{-- <div class="loading-failed">
               <span>Server Error</span>
               <a>Ok</a>
           </div> --}}
        </div>            
   </div>

</body>
<script src="/js/jquery.min.js"></script>
<script src="{{ asset('js/app.js')}}"></script>
</html>