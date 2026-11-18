@extends('layout.admin')

@section('title', 'Categories | Coda Admin')

@section('content')
<div class="page-title order-page-title">
    <h1>Categories</h1>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Add category</a>
</div>

@if (session('message'))
    <div class="alert {{ session('status') === 'success' ? 'alert-success' : 'alert-danger' }}" role="status">{{ session('message') }}</div>
@endif

<div class="panel-table">
    <x-category-table-component :categories="$categories" :keyword="$keyword"/>
</div>
@endsection
