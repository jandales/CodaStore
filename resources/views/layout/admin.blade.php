<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/fonts/fontawesome-free-5.15.3-web/css/all.css">
    <link href="froala-editor/css/froala_editor.pkgd.min.css" rel="stylesheet" type="text/css" />
    <script src="/js/front/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>Document</title>
</head>
<body>
    <div id="main">
        <div id="topbar">
            <div class="header">
                <div class="navbar-brand">
                    <div class="menubar">
                        <a href="#"><i class="fa fa-bars menubaricon"></i></a>
                    </div>
                    <div class="logo">
                        <img src="/img/logo-light-icon.png" alt="">
                        <a class="brandname" href="#">Store Admin</a>
                    </div>

                </div>
                <div class="navbar">
                    <div class="flex navbar-search">
                        <a class="open-close-toggle-search-bar" href="#"><i
                                class="fa fa-search"></i><span>Search</span></a>
                    </div>
                    <nav>
                        <ul class="inline">   
                            <li class="visit-store"><a href="/" class="visit-store"><i class="fas fa-store"></i><span class="hide-menu">Visit Store</span></a></li>                        
                            <li>
                                <div class="navbar-user">
                                    <div class="user-image">
                                        <img src="/img/user.png" alt="">
                                    </div>
                                </div>

                                <ul class="navbar-dropdownlist">
                                    <li><a href="{{ route('admin.account') }}"><span><i class="fa fa-user"></i></span>My Profile</a></li>
                                    <li><a href="#"><span><i class="fa fa-user"></i></span>Inbox</a></li>
                                    <li><a  onclick="document.getElementById('form-logout').submit();"><span><i class="fa fa-user"></i></span>sign out</a></li>
                                   
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
                <input type="text" name="search" placeholder="Search Here">
                <span class="open-close-toggle-search-bar"><i class="fa fa-times"></i></span>
            </div>
        </div>
        <div id="wrapper">
            <div class="side-bar">
                <nav>
                    <ul>
                        <li><a class="{{ (request()->is('admin/dashboard')) ? 'active' : ''}} " nav-item href="/admin/dashboard"><i class="fas fa-tachometer-alt"></i><span class="hide-menu">Dashbaord</span></a></li>
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
                                <li class="{{ (request()->is('admin/products/categories')) ? 'active' : ''}}"><a href="{{route('admin.categories')}}">Category</a></li>
                                <li class="{{ (request()->is('admin/products/inventory')) ? 'active' : ''}}"><a href="{{route('admin.inventory')}}">Inventory</a></li>
                                <li class="{{ (request()->is('admin/products/attributes')) ? 'active' : ''}}"><a href="{{route('admin.attributes')}}">Attributes</a></li>
                              </ul>
                        </li>
                        <li><a class="{{ (request()->is('admin/orders')) ? 'active' : '' }}" nav-item href="/admin/orders"><i class="fas fa-box"></i><span class="hide-menu">Orders</span></a></li>
                        <li><a class="{{ (request()->is('admin/coupons')) ? 'active' : '' }}" nav-item href="{{ route('admin.coupons') }}"><i class="fas fa-money-check-alt"></i><span class="hide-menu">Coupons</span></a></li>
                        <li><a class="{{ (request()->is('admin/reviews')) ? 'active' : '' }}" nav-item href="/admin/reviews"><i class="far fa-comments"></i><span class="hide-menu">Reviews</span></a></li>
                        <li><a class="{{ (request()->is('admin/customers')) ? 'active' : '' }}" nav-item href="/admin/customers"><i class="fas fa-users"></i><span class="hide-menu">Customers</span></a></li>
                        <li><a class="{{ (request()->is('admin/users')) ? 'active' : '' }}" nav-item href="{{ route('admin.users') }}"><i class="fas fa-users"></i><span class="hide-menu">Users</span></a></li>
                        <li><a class="{{ (request()->is('admin/settings')) ? 'active' : '' }}" nav-item href="{{ route('admin.settings') }}"><i class="fas fa-users"></i><span class="hide-menu">Settings</span></a></li>
                
                        

                    </ul>
                </nav>
            </div>
            <div class="page-wrapper">
                <div class="page">
                     @yield('content')
                 </div>
                 @extends('layout.footer')

