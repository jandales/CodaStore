@extends('layout.front.app')

@section('content') 
    <div class="homepage">
        {{-- <img src="/img/hero.jpg" alt=""> --}}
        {{-- <div class="row space-between">
            <div class="col-1">
                <p>Men Collection 2021</p>
                <h1>New Arrivals</h1>
                <a href="/shop" class="btn cffffff bgff523b mt-2">Shop now</a>
            </div>
            <div class="col-2">
                <img src="/img/front/main.png">
            </div>
        </div> --}}
        <a href="/shop" class="shopnow">SHOP NOW</a>
    </div>      
    <!--Featured Product-->
    <div class="container"> 
        <x-collection-component/>   
        <x-featured-product-component/>
    </div>         
@endsection




  