@extends('layout.admin')

@section('title', 'Add category | Coda Admin')

@section('content')
<div class="page-title order-page-title">
    <h1>Add category</h1>
    <a href="{{ route('admin.categories') }}" class="btn btn-light">All categories</a>
</div>

<div class="category-form-wrap">
    @include('admin.category._form')
</div>
@endsection
