@extends('layout.admin')

@section('title', 'Add attribute | Coda Admin')

@section('content')
<div class="page-title order-page-title">
    <h1>Add attribute</h1>
    <a href="{{ route('admin.attributes') }}" class="btn btn-light">All attributes</a>
</div>

<div class="category-form-wrap">
    @include('admin.attribute._form')
</div>
@endsection
