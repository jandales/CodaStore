@extends('layout.front.app')

@section('title', ($category === 'All Collection' ? 'All products' : ucfirst($category)) . ' | Coda')
@section('meta_description', $category === 'All Collection' ? 'Shop all Coda clothing: shirts, jackets, hoodies and everyday essentials in natural fabrics.' : 'Shop ' . $category . ' at Coda: considered essentials in natural fabrics, cut simply and made to last.')

@section('content')
    <div class="container shop-page">
        <header class="shop-head">
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <ol>
                    <li><a href="{{ route('home') }}">Home</a></li>
                    <li aria-current="page">Shop</li>
                </ol>
            </nav>
            <h1 class="shop-title capitalize">{{ $category === 'All Collection' ? 'All products' : $category }}</h1>
            @if (method_exists($products, 'total'))
                <p class="shop-count">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</p>
            @endif
        </header>
        <div class="productPage">
            <x-product-list-component :products="$products"/>
        </div>
    </div>
@endsection
