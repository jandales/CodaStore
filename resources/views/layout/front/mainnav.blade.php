<div class="container">
    <div class="navigation">
        <div class="navbar">     
            <div class="hamburger">
                <span><i class="fa-solid fa-bars"></i></span>
            </div>                   
            <div><a href="/" class="logo"><strong>Coda</strong> Store</a></div>
            <nav id="main-navigation"> 
                    <div class="close-navigation">
                        <span><i class="fa-solid fa-times"></i></span>
                    </div> 
                    <ul>
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="{{ route('shop') }}">Shop</a></li>
                        <li><a href="{{ route('about') }}">About</a></li>
                        <li><a href="{{ route('contact') }}">Contact</a></li>
                        <li class="md-hidden mt-auto"><a href="{{ route('account') }}">My Account</a></li>
                        <li class="md-hidden"><a  href="{{ route('logout') }}">Logout</a></li>
                    </ul> 
            </nav> 
            <nav id="navigation-icon"> 
                <ul>
                    <li><a id="btnsearch"><i class="fas fa-search"></i></a></li>
                    @guest
                        <li class="nav-item-md-hidden"><a href="{{ route('login') }}"><i class="fas fa-user"></i></a></li>    
                    @endguest
                    @auth
                    <li class="nav-item-md-hidden"><a href="{{ route('account') }}"><i class="fas fa-user"></i></a>
                        <ul>
                            <li><a href="{{ route('account') }}"><span><i class="fa-solid fa-user"></i></span> My Account</a></li>
                            <li><a href="{{ route('account.orders',["all"]) }}"><span><i class="fas fa-box"></i></span> My Orders</a></li>
                            <li><a  href="{{ route('logout') }}"><span><i class="fas fa-power-off"></i></span> Logout</a></li>
                        </ul> 
                    </li>    
                    @endauth
                    <li>                    
                        <div class="lst-icon-wrapper">
                             <a href="#" id="open-side-cart-modal"><i class="fas fa-shopping-cart"></i></a>
                         
                            <div class="icon-top-wrapper hidden">
                                <span class="cart-count"></span>
                            </div>
                        </div>                   
                    </li>
                   
                </ul>  
            </nav> 
        </div>
        <form class="form-search"  action="{{ route('search') }}" method="get">
            <div class="navigation-search">                        
                <input type="text" id="inputSearch" name="keyword" placeholder="Search Here....">
                <a class="close-search"><i class="fa fa-times" aria-hidden="true"></i></a>
            </div>
        </form>
    </div>
   
 
</div>

