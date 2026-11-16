<div class="container">
    <div class="navigation">
        <div class="navbar">
            <button type="button" class="hamburger" aria-label="Open menu" aria-controls="main-navigation">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <div class="logo"><a href="{{ route('home') }}" class="logo wordmark" aria-label="Coda home">Coda</a></div>
            <nav id="main-navigation" aria-label="Main">
                    <button type="button" class="close-navigation" aria-label="Close menu">
                        <i class="fa-solid fa-times" aria-hidden="true"></i>
                    </button>
                    <ul>
                        <li><a href="{{ route('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>Home</a></li>
                        <li><a href="{{ route('shop') }}" @if(request()->routeIs('shop*')) aria-current="page" @endif>Shop</a></li>
                        <li><a href="{{ route('about') }}" @if(request()->routeIs('about')) aria-current="page" @endif>About</a></li>
                        <li><a href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Contact</a></li>
                        @auth
                            <li class="md-hidden mt-auto"><a href="{{ route('account') }}">My account</a></li>
                            <li class="md-hidden"><a href="{{ route('logout') }}">Log out</a></li>
                        @else
                            <li class="md-hidden mt-auto"><a href="{{ route('login') }}">Sign in</a></li>
                        @endauth
                    </ul>
            </nav>
            <nav id="navigation-icon" aria-label="Shortcuts">
                <ul>
                    <li><button type="button" id="btnsearch" class="icon-btn" aria-label="Search"><i class="fas fa-search" aria-hidden="true"></i></button></li>
                    @guest
                        <li class="nav-item-md-hidden"><a href="{{ route('login') }}" class="icon-btn" aria-label="Sign in"><i class="fas fa-user" aria-hidden="true"></i></a></li>
                    @endguest
                    @auth
                    <li class="nav-item-md-hidden"><a href="{{ route('account') }}" class="icon-btn" aria-label="Account" aria-haspopup="true"><i class="fas fa-user" aria-hidden="true"></i></a>
                        <ul>
                            <li><a href="{{ route('account') }}"><span><i class="fa-solid fa-user" aria-hidden="true"></i></span> My account</a></li>
                            <li><a href="{{ route('account.orders',["all"]) }}"><span><i class="fas fa-box" aria-hidden="true"></i></span> My orders</a></li>
                            <li><a href="{{ route('logout') }}"><span><i class="fas fa-power-off" aria-hidden="true"></i></span> Log out</a></li>
                        </ul>
                    </li>
                    @endauth
                    <li>
                        <div class="lst-icon-wrapper">
                            <a href="#" id="open-side-cart-modal" class="icon-btn" aria-label="Shopping bag"><i class="fas fa-shopping-bag" aria-hidden="true"></i></a>
                            <div class="icon-top-wrapper hidden" aria-live="polite">
                                <span class="cart-count"></span>
                            </div>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
        <form class="form-search" action="{{ route('search') }}" method="get" role="search">
            <div class="navigation-search">
                <i class="fas fa-search" aria-hidden="true"></i>
                <label for="inputSearch" class="sr-only">Search products</label>
                <input type="text" id="inputSearch" name="keyword" placeholder="Search for shirts, jackets, knitwear">
                <button type="button" class="close-search icon-btn" aria-label="Close search"><i class="fa fa-times" aria-hidden="true"></i></button>
            </div>
        </form>
    </div>
</div>
