@extends('layout.front.app')

@section('title', ($keyword ? 'Search: ' . $keyword : 'Search') . ' | Coda')

@section('content')
    <div class="container shop-page">
        <header class="shop-head">
            <h1 class="shop-title">{{ $keyword ? 'Results for "' . $keyword . '"' : 'Search' }}</h1>
            <form class="search-form" action="{{ route('search') }}" method="get" role="search">
                <label for="search-page-input" class="sr-only">Search products</label>
                <i class="fas fa-search" aria-hidden="true"></i>
                <input id="search-page-input" type="search" name="keyword" placeholder="Search for shirts, jackets, knitwear" value="{{ $keyword }}">
                <button type="submit" class="btn btn-dark">Search</button>
            </form>
        </header>
        @if ($keyword != null)
            <div class="productPage">
                <x-product-list-component :products="$products"/>
            </div>
        @endif
    </div>
@endsection
