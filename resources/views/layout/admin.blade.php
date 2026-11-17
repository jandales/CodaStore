<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/main.css')}}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'Admin | Coda')</title>
</head>
@php
    $admin = auth()->guard('admin')->user();
    $pendingOrders = \App\Models\Order::where('status', 'confirmed')->count();
@endphp
<body class="admin-body">
    <div id="main">
        <div id="wrapper">
            <aside class="side-bar" aria-label="Admin navigation">
                <div class="sb-brand">
                    <a class="admin-wordmark" href="{{ route('admin.dashboard') }}"><i class="fas fa-bag-shopping" aria-hidden="true"></i><b>Coda<span>.</span></b></a>
                </div>

                <a href="{{ route('admin.account') }}" class="sb-profile">
                    <img src="{{ $admin->avatar() }}" alt="">
                    <span class="sb-profile__text">
                        <strong>{{ trim($admin->fullName()) ?: $admin->username }}</strong>
                        <small>{{ $admin->userRole() }}</small>
                    </span>
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </a>

                <nav aria-label="Admin">
                    <p class="sb-label">Menu</p>
                    <ul>
                        <li><a class="{{ request()->is('admin/dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="fas fa-house" aria-hidden="true"></i><span class="hide-menu">Dashboard</span></a></li>
                        <li class="nav-dropdown {{ request()->is('admin/products*') ? 'active' : '' }}">
                            <div class="nav-dropdown-btn">
                                <a><i class="fas fa-box-open" aria-hidden="true"></i><span class="hide-menu">Products</span></a>
                                <i class="fas fa-angle-down arrow" aria-hidden="true"></i>
                            </div>
                            <ul class="sub-menu {{ request()->is('admin/products*') ? 'show' : '' }}">
                                <li class="{{ request()->is('admin/products') ? 'active' : '' }}"><a href="{{ route('admin.products') }}">Items</a></li>
                                <li class="{{ request()->is('admin/products/categories*') ? 'active' : '' }}"><a href="{{ route('admin.categories') }}">Categories</a></li>
                                <li class="{{ request()->is('admin/products/inventory*') ? 'active' : '' }}"><a href="{{ route('admin.inventory') }}">Inventory</a></li>
                                <li class="{{ request()->is('admin/products/attributes*') ? 'active' : '' }}"><a href="{{ route('admin.attributes') }}">Attributes</a></li>
                            </ul>
                        </li>
                        <li><a class="{{ request()->is('admin/orders*') ? 'active' : '' }}" href="{{ route('admin.orders') }}"><i class="fas fa-receipt" aria-hidden="true"></i><span class="hide-menu">Orders</span>@if($pendingOrders)<span class="sb-badge" aria-label="{{ $pendingOrders }} to ship">{{ $pendingOrders }}</span>@endif</a></li>
                        <li><a class="{{ request()->is('admin/payments*') ? 'active' : '' }}" href="{{ route('admin.payments') }}"><i class="fas fa-credit-card" aria-hidden="true"></i><span class="hide-menu">Payments</span></a></li>
                        <li><a class="{{ request()->is('admin/coupons*') ? 'active' : '' }}" href="{{ route('admin.coupons') }}"><i class="fas fa-ticket" aria-hidden="true"></i><span class="hide-menu">Coupons</span></a></li>
                        <li><a class="{{ request()->is('admin/review*') ? 'active' : '' }}" href="{{ route('admin.reviews') }}"><i class="far fa-comments" aria-hidden="true"></i><span class="hide-menu">Reviews</span></a></li>
                    </ul>

                    <p class="sb-label">People</p>
                    <ul>
                        <li><a class="{{ request()->is('admin/customers*') ? 'active' : '' }}" href="{{ route('admin.customers') }}"><i class="fas fa-user-group" aria-hidden="true"></i><span class="hide-menu">Customers</span></a></li>
                        @if($admin->is_admin())
                            <li><a class="{{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users') }}"><i class="fas fa-user-shield" aria-hidden="true"></i><span class="hide-menu">Staff users</span></a></li>
                        @endif
                    </ul>

                    <p class="sb-label">Sales channel</p>
                    <ul>
                        <li><a href="/" target="_blank" rel="noopener"><i class="fas fa-store" aria-hidden="true"></i><span class="hide-menu">Online store</span><i class="fas fa-arrow-up-right-from-square sb-ext" aria-hidden="true"></i></a></li>
                    </ul>
                </nav>
            </aside>

            <div class="admin-main">
                <header id="topbar">
                    <div class="menubar">
                        <a href="#" aria-label="Toggle menu"><i class="fa fa-bars menubaricon" aria-hidden="true"></i></a>
                    </div>
                    <form class="tb-search" action="{{ route('admin.products.search') }}" method="get" role="search">
                        <label for="admin-search" class="sr-only">Search products</label>
                        <input id="admin-search" type="search" name="keyword" placeholder="Search products" value="{{ request('keyword') }}">
                        <button type="submit" aria-label="Search"><i class="fa fa-search" aria-hidden="true"></i></button>
                    </form>
                    <div class="tb-actions">
                        <span class="tb-date"><i class="far fa-calendar" aria-hidden="true"></i> {{ now()->format('j F Y') }}</span>
                        <a href="/" class="tb-icon" target="_blank" rel="noopener" aria-label="View store"><i class="fas fa-store" aria-hidden="true"></i></a>
                        <div class="navbar-user">
                            <button type="button" class="user-image" aria-label="Account menu" aria-haspopup="true">
                                <img src="{{ $admin->avatar() }}" alt="">
                                <i class="fas fa-chevron-down" aria-hidden="true"></i>
                            </button>
                            <ul class="navbar-dropdownlist">
                                <li><a href="{{ route('admin.account') }}"><i class="far fa-user" aria-hidden="true"></i> My profile</a></li>
                                <li><a href="#" onclick="event.preventDefault(); document.getElementById('form-logout').submit();"><i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i> Sign out</a></li>
                            </ul>
                            <form id="form-logout" action="{{ route('admin.logout') }}" method="post">@csrf</form>
                        </div>
                    </div>
                </header>
                {{-- Kept for the admin JS search toggle; not shown. --}}
                <div class="searchbar" hidden></div>

            <div class="page-wrapper">
                <div class="page">
                     @yield('content')
                 </div>
                 @extends('layout.footer')

