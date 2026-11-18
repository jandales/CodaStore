@extends('layout.admin')

@section('title', 'Attributes | Coda Admin')

@section('content')
<div class="page-title order-page-title">
    <h1>Attributes</h1>
    <a href="{{ route('admin.attributes.create') }}" class="btn btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Add attribute</a>
</div>

@if (session('message'))
    <div class="alert {{ session('status') === 'success' ? 'alert-success' : 'alert-danger' }}" role="status">{{ session('message') }}</div>
@endif

<div class="panel-table">
    <x-attribute-table-component :list="$attributes" :keyword="$keyword"/>
</div>
@endsection
