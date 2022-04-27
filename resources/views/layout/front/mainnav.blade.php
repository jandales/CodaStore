<div class="container navigation">
    <div class="navbar">                        
        <div><a href="/" class="logo"><strong>Coda</strong> Store</a></div>
        <nav id="main-navigation"> 
                <ul>
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li><a href="{{ route('shop') }}">Shop</a></li>
                    <li><a href="{{ route('about') }}">About</a></li>
                    <li><a href="{{ route('contact') }}">Contact</a></li>
                </ul> 
        </nav> 
        <nav id="navigation-icon"> 
            <ul>
                <li><a id="btnsearch"><i class="fas fa-search"></i></a></li>
                @guest
                    <li><a href="{{ route('login') }}"><i class="fas fa-user"></i></a></li>    
                @endguest
                @auth
                <li><a href="{{ route('account') }}"><i class="fas fa-user"></i></a>
                    <ul>
                        <li><a href="{{ route('account') }}"><span><i class="fa-solid fa-user"></i></span> My Account</a></li>
                        <li><a href="{{ route('account.orders',["all"]) }}"><span><i class="fas fa-box"></i></span> My Orders</a></li>
                        <li><a  href="{{ route('logout') }}"><span><i class="fas fa-power-off"></i></span> Logout</a></li>
                    </ul> 
                </li>    
                @endauth
                <li>                    
                    <div class="lst-icon-wrapper">
                         <a href="#"  data-modal-target="sidecartModal"><i class="fas fa-shopping-cart"></i></a>
                     
                        <div class="icon-top-wrapper hidden">
                            <span class="cart-count"></span>
                        </div>
                    </div>                   
                </li>
               
            </ul>  
        </nav> 
    </div>
    <form action="{{ route('search') }}" method="get">
        <div class="navigation-search">                        
            <input type="text" id="inputSearch" name="keyword" placeholder="Search Here....">
            <a class="close-search"><i class="fa fa-times" aria-hidden="true"></i></a>
        </div>
    </form>
 
</div>

