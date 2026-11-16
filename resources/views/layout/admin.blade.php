<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/main.css')}}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'Admin | Coda')</title>
</head>
<body class="admin-body">
    <div id="main">
        <div id="topbar">
            <div class="header">
                <div class="navbar-brand">
                    <div class="menubar">
                        <a href="#" aria-label="Toggle menu"><i class="fa fa-bars menubaricon" aria-hidden="true"></i></a>
                    </div>
                    <div class="logo">
                        <a class="brandname admin-wordmark" href="{{ route('admin.dashboard') }}">Coda <span>Admin</span></a>
                    </div>

                </div>
                <div class="navbar">
                    <div class="flex navbar-search">
                        <a class="open-close-toggle-search-bar" href="#"><i class="fa fa-search" aria-hidden="true"></i><span>Search</span></a>
                    </div>
                    <nav>
                        <ul class="inline">   
                            <li class="visit-store"><a href="/" class="visit-store"><i class="fas fa-store" aria-hidden="true"></i><span class="hide-menu">View store</span></a></li>                        
                            <li>
                                <div class="navbar-user">
                                    <div class="user-image">
                                        <img src="{{auth()->guard('admin')->user()->avatar()}}" alt="Your profile">
                                    </div>
                                </div>

                                <ul class="navbar-dropdownlist">
                                    <li><a href="{{ route('admin.account') }}"><span><i class="fa fa-user" aria-hidden="true"></i></span>My profile</a></li>
                                    {{-- <li><a href="#"><span><i class="fa fa-user"></i></span>Inbox</a></li> --}}
                                    <li><a href="#" onclick="event.preventDefault(); document.getElementById('form-logout').submit();"><span><i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i></span>Sign out</a></li>
                                   
                                </ul>
                                <form id="form-logout" action="{{ route('admin.logout') }}" method="post">@csrf</form>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

        </div>
        <div class="searchbar">
            <div class="form-search">
                <input type="text" name="search" placeholder="Search" aria-label="Search">
                <span class="open-close-toggle-search-bar" role="button" aria-label="Close search"><i class="fa fa-times" aria-hidden="true"></i></span>
            </div>
        </div>
        <div id="wrapper">
            <div class="side-bar">
                <nav aria-label="Admin">
                    <ul>
                        <li><a class="{{ (request()->is('admin/dashboard')) ? 'active' : ''}} " nav-item href="/admin/dashboard"><i class="fas fa-tachometer-alt"></i><span class="hide-menu">Dashboard</span></a></li>
                        <li class="nav-dropdown {{ (request()->is('admin/products*')) ? 'active' : '' }} ">                            
                            <div class="nav-dropdown-btn">
                                <a>
                                    <i class="far fa-clipboard"></i>
                                    <span class="hide-menu">Products</span> 
                                </a>
                                <i class="fas fa-angle-down arrow"></i>
                            </div>
                              <ul class="sub-menu {{ (request()->is('admin/products*')) ? 'show' : '' }} ">
                                <li class="{{ (request()->is('admin/products')) ? 'active' : ''}}"><a href="{{route('admin.products')}}">Items</a></li>
                                <li class="{{ (request()->is('admin/products/categories*')) ? 'active' : ''}}"><a href="{{route('admin.categories')}}">Category</a></li>
                                <li class="{{ (request()->is('admin/products/inventory*')) ? 'active' : ''}}"><a href="{{route('admin.inventory')}}">Inventory</a></li>
                                <li class="{{ (request()->is('admin/products/attributes*')) ? 'active' : ''}}"><a href="{{route('admin.attributes')}}">Attributes</a></li>
                              </ul>
                        </li>
                        <li><a class="{{ (request()->is('admin/orders*')) ? 'active' : '' }}" nav-item href="{{route('admin.orders')}}"><i class="fas fa-box"></i><span class="hide-menu">Orders</span></a></li>
                        <li><a class="{{ (request()->is('admin/payments*')) ? 'active' : '' }}" nav-item href="{{ route('admin.payments') }}"><i class="fas fa-credit-card"></i><span class="hide-menu">Payments</span></a></li>
                        <li><a class="{{ (request()->is('admin/coupons*')) ? 'active' : '' }}" nav-item href="{{ route('admin.coupons') }}"><i class="fas fa-money-check-alt"></i><span class="hide-menu">Coupons</span></a></li>
                        <li><a class="{{ (request()->is('admin/reviews*')) ? 'active' : '' }}" nav-item href="{{ route("admin.reviews") }}"><i class="far fa-comments"></i><span class="hide-menu">Reviews</span></a></li>
                        <li><a class="{{ (request()->is('admin/customers*')) ? 'active' : '' }}" nav-item href="{{route('admin.customers')}}"><i class="fas fa-users"></i><span class="hide-menu">Customers</span></a></li>
                        @if(auth()->guard('admin')->user()->is_admin())
                        {{-- <li><a class="{{ (request()->is('admin/inboxes*')) ? 'active' : '' }}" nav-item href="{{route('admin.inbox')}}"><i class="fas fa-inbox"></i><span class="hide-menu">Inbox</span></a></li> --}}
                             <li><a class="{{ (request()->is('admin/users*')) ? 'active' : '' }}" nav-item href="{{ route('admin.users') }}"><i class="fas fa-users"></i><span class="hide-menu">Users</span></a></li>
                             {{-- <li  class="nav-dropdown {{ (request()->is('admin/setting*')) ? 'active' : '' }} " >
                                <div class="nav-dropdown-btn">
                                    <a><i class="fa-solid fa-gear"></i><span class="hide-menu">Settings</span></a>
                                    <i class="fas fa-angle-down arrow"></i>
                                </div>
                                <ul class="sub-menu {{ (request()->is('admin/setting*')) ? 'show' : '' }} ">
                                    <li class="{{ (request()->is('admin/setting/general')) ? 'active' : ''}}"><a href="{{ route('admin.setting.general')}}">General</a></li>
                                    <li class="{{ (request()->is('admin/setting/campany')) ? 'active' : ''}}"><a href="{{route('admin.setting.campany')}}">Campany</a></li>
                                    <li class="{{ (request()->is('admin/setting/shipping-method*')) ? 'active' : ''}}"><a href="{{ route('admin.shipping.method') }}">Shipping</a></li>
                                    
                                    <li class="{{ (request()->is('admin/setting/social')) ? 'active' : ''}}"><a href="{{route('admin.setting.social')}}">Social</a></li>                                                                 
                                </ul>
                            </li> --}}
                        @endif      
                
                        {{-- <li class="{{ (request()->is('admin/setting/emailer')) ? 'active' : ''}}"><a href="{{route('admin.setting.emailer')}}">Email</a></li>   --}}

                    </ul>
                </nav>
            </div>
            <div class="page-wrapper">
                <div class="page">
                     @yield('content')
                 </div>
                 @extends('layout.footer')

