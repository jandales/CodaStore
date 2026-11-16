<div class="sidebar account-nav">
    <div class="account-nav__intro">
        <span class="eyebrow">Your account</span>
        <p class="account-nav__name">{{ auth()->user()->name }}</p>
    </div>
    <nav class="sidebar-menu" aria-label="Account">
        <ul>
            <li><a class="{{ request()->is('account', 'account/profile*', 'account/edit*', 'account/password*', 'account/upload*') ? 'active' : '' }}" @if(request()->is('account', 'account/profile*')) aria-current="page" @endif href="{{ route('account') }}"><span><i class="fas fa-user" aria-hidden="true"></i></span>Profile</a></li>
            <li><a class="{{ request()->is('account/orders*', 'orders*') ? 'active' : '' }}" @if(request()->is('account/orders*', 'orders*')) aria-current="page" @endif href="{{ route('account.orders', ['all']) }}"><span><i class="fas fa-box" aria-hidden="true"></i></span>Orders</a></li>
            <li><a class="{{ request()->is('account/payment-option*') ? 'active' : '' }}" @if(request()->is('account/payment-option*')) aria-current="page" @endif href="{{ route('account.payment-option') }}"><span><i class="fas fa-credit-card" aria-hidden="true"></i></span>Payment methods</a></li>
            <li><a class="{{ request()->is('account/shipping-address*') ? 'active' : '' }}" @if(request()->is('account/shipping-address*')) aria-current="page" @endif href="{{ route('account.shippingaddress') }}"><span><i class="far fa-address-card" aria-hidden="true"></i></span>Addresses</a></li>
            <li class="account-nav__logout"><a href="{{ route('logout') }}"><span><i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i></span>Log out</a></li>
        </ul>
    </nav>
</div>
