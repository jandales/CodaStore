@extends('layout.front.app')

@section('content') 
    <div class="container">    
        <div class="pagetitle">
            <h1 class="capitalize">{{$category}}</h1>
        </div>
        <div class="productPage mt-2">                            
            <x-product-list-component :products="$products"/>
        </div>
    </div> 
@endsection