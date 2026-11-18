<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Coda | Modern clothing store')</title>  
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=4">  
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    @include('layout.front.seo')

    
</head>

<body>

    <div id="flex">      
        {{-- @if(config('app.env') != 'local') 
            <div style="width:100%; background-color: #fff3cd;">
                <div class="container"  style="padding-top: .5rem; padding-bottom: .5rem">
                    <label for="">This website for demo purpose only</label>
                </div>
            </div>
        @endif --}}
       
     
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
        <div class="modal-sidebar bag-drawer" role="dialog" aria-modal="true" aria-labelledby="bag-drawer-title">
            <div class="bag-drawer__head">
                <h2 id="bag-drawer-title" class="sidebar-heading">Your bag</h2>
                <div class="sidebar-close">
                    <button type="button" class="close-drawer icon-btn" aria-label="Close bag"><i class="fas fa-times" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="sidebar-body">

            </div>

            <div class="sidebar-footer">
                <div class="bag-drawer__subtotal">
                    <span>Subtotal</span>
                    <span class="cart-item-total"></span>
                </div>
                <p class="bag-drawer__note">Shipping and discounts calculated at checkout.</p>
                <a href="{{ route('checkout.information') }}" class="btn btn-dark">Checkout</a>
                <a href="{{ route('cart') }}" class="btn btn-default">View bag</a>
            </div>
        </div>
    </div>

   

    <script src="/js/jquery.min.js"></script>
    <script src="{{ asset('js/app.js')}}"></script>
    <script src="{{ asset('js/saving-forms.js') }}?v=2"></script>




    
  
  
</body>

</html>