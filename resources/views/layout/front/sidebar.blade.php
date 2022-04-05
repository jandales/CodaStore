<div class="sidebar">
    {{-- <div class="avatar center">
        <img src="{{ auth()->user()->imagePath }}" alt="">
        <a href="{{ route('account.upload') }}"><span><i class="fa fa-camera" aria-hidden="true"></i></span></a>
    </div> --}}


    <div class="sidebar-menu">
        <ul>
            {{-- <li><a  class="{{(request()->is('account/dashboard')) ? 'active' : ''}}"  href="{{ route('account.dashboard') }}"><span><i class="fas fa-tachometer-alt"></i></span>Dashbaord</a></li> --}}
            <li><a class="{{(request()->is('account')) ? 'active' : ''}}"  href="{{ route('account') }}"><span><i class="fas fa-user"></i></span>Profile</a></li>
            <li><a class="{{(request()->is('account/orders/all')) ? 'active' : ''}}"  href="{{ route('account.orders',['all'])}}"><span><i class="fas fa-box"></i></span>Orders</a></li>          
            <li><a class="{{(request()->is('account/payment-option')) ? 'active' : ''}}"  href="{{ route('account.payment-option')}}"><span><i class="fas fa-box"></i></span>Payment Method</a></li> 
            <li ><a class="{{(request()->is('account/shipping-address')) ? 'active' : ''}}"  href="{{ route('account.shippingaddress') }}"><span><i class="far fa-address-card"></i></span>Shipping Address</a></li>
            <li><a class="{{(request()->is('wishlists')) ? 'active' : ''}}" href="{{ route('wishlists') }}"><span><i class="fas fa-heart"></i></span>Wishlist</a></li>          
            <li><a nav-item href="{{ route('logout') }}"><span><i class="fas fa-power-off"></i></span>Logout</a></li>
        </ul>
    </div>
</div>
