@extends('checkout')

@section('content') 

        @if (session('success'))
                <div class="alert alert-success mt-1">{{ session('success')}}</div>
        @endif

        @if (session('message'))
                <div class="alert alert-warning mt-1">{{ session('message')}}</div>
        @endif  

        <div class="center mt-5">
                <h1 class="text-center text-2xl">Thank you Purchasing Our Product</h1>
        </div>

        <a class="mt-2 btn btn-theme el-center" href="{{ route('shop') }}">Continue Shopping</a>

@endsection


   
    
     