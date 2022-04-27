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

   

    <script src="/js/front/jquery.min.js"></script>
    <script src="/js/front/main.js"></script>
    <script src="/js/front/sticky-header.js"></script>
    <script src="/js/front/tabs.js"></script>
    <script src="/js/front/modal.js"></script>
    <script src="/js/front/sidecart.js"></script>  
    <script src="/js/front/tableaction.js"></script>
    <script src="/js/front/users/users.js"></script> 
    <script src="/js/message.js"></script>
    <script src="/js/admin/arrayFunc.js"></script>
    <script src="/js/front/product.js"></script>
    <script src="/js/front/image-slider.js"></script>
    <script src="/js/product/ratingevent.js"></script>
    <script src="/js/product/addwishlist.js"></script>
    <script src="/js/front/variants.js"></script>
    <script src="/js/validator.js"></script>

    
  
  
</body>

</html>