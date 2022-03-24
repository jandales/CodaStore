<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>photography</title>  
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/fonts/fontawesome-free-5.15.3-web/css/all.css">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 

   
</head>

<body>

    <div id="flex">   

        <div class="header-container">
            <div class="header">
                <div class="container navigation">
                    <div class="navbar">                        
                        <div><a href="/" class="logo"><strong>Coda</strong> Store</a></div>
                        <nav id="main-navigation">          
                                    
                                 <ul>
                                        <li><a href="/">Home</a></li>
                                        <li><a href="/shop">Shop</a></li>
                                        <li><a href="/about">About</a></li>
                                        <li><a href="/contact">Contact</a></li>
                                </ul>            
                            
                        </nav>                    

                    
                        <nav id="navigation-icon">          
                                    
                            <ul>
                                <li><a  class="search"><i class="fas fa-search"></i></a></li>
                                @if (session('adminLogined'))
                                    <li><a href="{{ route('admin') }}">Dashbaord</a></li>
                                @endif
                                @auth
                                    <li><a href="/account"><i class="fas fa-user"></i></a> 
                                    </li>                                
                                    <li><a href="/cart"><i class="fas fa-cart-plus"></i></a></li>
                                    <li><a id="wish-lists-trigger" data-modal-target="modal-wish-lists"><i class="fas fa-heart"></i></a></li>
                                    <li><a href="{{ route('logout') }}"><i class="fas fa-sign-out-alt"></i></a></li>
                                    <li class="hamburger"><a id="btnmenu" onclick="menutoggle()"><i id="hamburger-icon" class="fa fa-bars"></i></a></li>
                                @endauth

                                @if (session('adminLogined'))
                                @else
                                    @guest
                                    <li><a href="/login">Login</a></li>
                                    <li><a href="/register">Register</a></li>
                                    @endguest
                                @endif

                               
                                
                                
                                           
                            
                            </ul>            
                    
                
                            </nav>
                        
                    
                    </div>

                    <div class="navigation-search">                        
                        <input type="text" id="inputSearch" placeholder="Search Here....">
                        <a class="close-search"><i class="fa fa-times" aria-hidden="true"></i></a>
                    </div>
                 
                </div>
            </div>
        </div>

        
              @yield('content')  
     
        
        

       
    </div>
   

    <script src="/js/front/jquery.min.js"></script>
    {{-- <script src="/js/front/main.js"></script>
    <script src="/js/front/sticky-header.js"></script>
    <script src="/js/front/tabs.js"></script>

    <script src="/js/front/users/users.js"></script> --}}
  
</body>

</html>