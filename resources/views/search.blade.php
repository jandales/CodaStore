@extends('layout.front.app')
@section('content') 
    <div class="container"> 
        <div class="productPage mt-4">    
            <div class="search-page mb-2">
                <h2>Search results</h2> 
                <form  action="{{ route('search') }}" method="get">
                    <div class="input-search">                        
                        <input type="text"  name="keyword" placeholder="Search Here...." value="{{$keyword}}">
                        <span class="close-search"><i class="fa fa-search" aria-hidden="true"></i></span>
                    </div>
                </form>  
            </div>      
            <x-product-list-component :products="$products"/>
        </div>
    </div>  
@endsection