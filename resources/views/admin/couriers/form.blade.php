@extends('layout.admin')

@section('title', ($courier->exists ? $courier->name : 'Add courier') . ' | Coda Admin')

@section('content')
<div class="page-title order-page-title">
    <h1>{{ $courier->exists ? $courier->name : 'Add courier' }}</h1>
    <a href="{{ route('admin.couriers') }}" class="btn btn-light">All couriers</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger" role="alert">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
@endif

<form method="post" action="{{ $courier->exists ? route('admin.couriers.update', $courier) : route('admin.couriers.store') }}" class="panel courier-form">
    @csrf
    @if ($courier->exists) @method('put') @endif
    <div class="panel-body">
        <div class="form-block">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" maxlength="80" required value="{{ old('name', $courier->name) }}" placeholder="e.g. J&T Express">
        </div>
        <div class="form-block">
            <label for="tracking_url">Tracking link <span class="label-hint">(optional)</span></label>
            <input type="text" id="tracking_url" name="tracking_url" maxlength="500" value="{{ old('tracking_url', $courier->tracking_url) }}" placeholder="https://courier.example/track?no={number}" aria-describedby="tracking-help">
            <p id="tracking-help" class="form-help">The courier's public tracking page, with <code>{number}</code> where the tracking number goes. When set, customers get a "Track parcel" button in the shipped email and on their order page.</p>
        </div>
        <div class="form-block">
            <label for="sort_order">Position in the list</label>
            <input type="number" id="sort_order" name="sort_order" min="0" step="1" value="{{ old('sort_order', $courier->sort_order) }}" aria-describedby="sort-help">
            <p id="sort-help" class="form-help">Lower numbers come first in the Courier dropdown when shipping.</p>
        </div>
        <label class="sale-toggle"><input type="checkbox" name="active" value="1" {{ old('active', $courier->active) ? 'checked' : '' }}> Active (offered when shipping an order)</label>
        <div class="flex gap10 mt20">
            <button class="btn btn-primary">Save</button>
        </div>
    </div>
</form>

@if ($courier->exists)
    @if ($courier->orders()->exists())
        <p class="form-help mt20">Used on {{ $courier->orders()->count() }} {{ \Illuminate\Support\Str::plural('order', $courier->orders()->count()) }}, so it can't be deleted. Untick "Active" to stop offering it.</p>
    @else
        <form method="post" action="{{ route('admin.couriers.destroy', $courier) }}" class="mt20" onsubmit="return confirm('Delete this courier?')">
            @csrf
            @method('delete')
            <button class="btn btn-danger">Delete courier</button>
        </form>
    @endif
@endif
@endsection
