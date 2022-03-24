<div class="topnav-wrapper">
    <div class="container">                      
        <nav id="topnav">       
            <p>Free Shipping for Standard over php 500</p>                        
            <ul>
                @auth('admin')
                <li><a href="{{ route('admin.dashboard') }}">Dashbaord</a></li>
                @endauth
                <li><a href="#">Customer Care</a></li>  
                @auth                                
                <li class="relative"><a href="#">My Account</a>
                    <ul>
                        <li><a href="{{ route('account') }}"><span><i class="fas fa-user"></i></span> My Account</a></li>
                        <li><a href="{{ route('account.orders',["all"]) }}"><span><i class="fas fa-box"></i></span> My Orders</a></li>
                        <li><a  href="{{ route('logout') }}"><span><i class="fas fa-power-off"></i></span> Logout</a></li>
                    </ul> 
                </li>
                <li class="hamburger"><a id="btnmenu" onclick="menutoggle()"><i id="hamburger-icon" class="fa fa-bars"></i></a></li>
                @endauth
                @if ( !auth()->guard('admin')->check() )  
                    @guest('web')
                        <li><a href="/login">Login</a></li>
                        <li><a href="/register">Register</a></li>
                    @endguest
                @endif
            </ul>
        </nav>
    </div>
</div>   

