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

    <div id="flex">
        @auth('admin')
            <div style="width:100%; background:#222">
                <div class="container" style="padding-top: .5rem; padding-bottom: .5rem">
                    <a href="/admin" style="color:#fff;">Dashbaord</a>
                </div>
            </div> 
        @endauth
        @if(config('app.env') != 'local') 
            <div style="width:100%; background-color: #fff3cd;">
                <div class="container"  style="padding-top: .5rem; padding-bottom: .5rem">
                    <label for="">This website for demo purpose only</label>
                </div>
            </div>
        @endif
       
     
        {{-- @if(!request()->routeIs(['login','register'])) --}}
            <div class="header-container">                 
                {{-- @include('layout.front.topnav')                  --}}
                <div class="header">              
                    @include('layout.front.mainnav') 
                </div>
            </div>
        {{-- @endif   --}}

        @yield('content')

        {{-- @if(!request()->routeIs(['register', 'login']))   --}}
            @include('layout.front.footer')
        {{-- @endif   --}}


    </div>

   
    
    <div id="msg-popup" class="pop-msg-overlay">
            <div class="msg-wrapper">                        
                <div class="msg-content success">                            
                    <div class="flex">
                        <div class="msg-icon">
                            <span class="inline-msg"><i class="fas fa-check-circle"></i></span>
                        </div>
                        <div class="msg ml-1">
                            <h2 class="title"></h2>                                
                            <p class="message"></p>                                  
                        </div>
                    </div>                           
                    <div class="msg-footer flex justify-content-flex-end">
                        <div msg-close="this" class="btn btn-success">OK</div>
                    </div>
                
                </div>
            </div>
    </div>

    <div class="modal" id="sidecartModal">
        <div class="modal-sidebar">
            <div class="sidebar-close">                    
                <span class="close-drawer"><i class="fas fa-times"></i></span>
            </div>         
            <div class="sidebar-heading">
                <p>Shopping Cart</p>
            </div>
            <div class="sidebar-body">
               
            </div>
           
            <div class="sidebar-footer">
                <div class="flex space-between">
                    <span class=" mb-1">Subtotal</span>
                    <span class="cart-item-total"></span>
                </div>
               
                <div class="flex gap10">
                    <a href="{{ route('cart')}}" class="btn btn-dark w-6">VIEW CART</a>
                    <a href="{{ route('checkout.information')}}" class="btn btn-dark w-6">CHECKOUT</a>
                </div>
            </div>
        </div> 
    </div>

   

    <script src="/js/jquery.min.js"></script>
    <script src="{{ asset('js/app.js')}}"></script>




    
  
  
</body>

</html>