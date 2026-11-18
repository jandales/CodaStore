@extends('layout.admin')

@section('title', 'Couriers | Coda Admin')

@section('content')
<div class="page-title order-page-title">
    <h1>Couriers</h1>
    <a href="{{ route('admin.couriers.create') }}" class="btn btn-primary"><i class="fas fa-plus" aria-hidden="true"></i> Add courier</a>
</div>

@if (session('success'))
    <div class="alert alert-success" role="status">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
@endif

<div class="panel-table">
    <table class="table courier-table">
        <thead>
            <tr>
                <th scope="col">Courier</th>
                <th scope="col">Tracking link</th>
                <th scope="col">Orders</th>
                <th scope="col">Status</th>
                <th scope="col"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($couriers as $courier)
                <tr>
                    <td><a class="link-primary" href="{{ route('admin.couriers.edit', $courier) }}">{{ $courier->name }}</a></td>
                    <td class="courier-link">{{ $courier->tracking_url ?: '—' }}</td>
                    <td>{{ $courier->orders_count }}</td>
                    <td><span class="status {{ $courier->active ? 'bg-success' : 'bg-warning' }}">{{ $courier->active ? 'Active' : 'Off' }}</span></td>
                    <td class="text-right">
                        <a href="{{ route('admin.couriers.edit', $courier) }}" class="btn btn-light">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><p class="timeline-empty">No couriers yet. Add the couriers you ship with.</p></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<p class="form-help mt20">Turned-off couriers aren't offered when shipping, but orders that used them still show their name and tracking link.</p>
@endsection
