@extends('layout.admin')

@section('title', 'No access | Coda Admin')

@section('content')
@php $area = \App\Models\Admin::PERMISSIONS[$permission][0] ?? ($permission === 'staff' ? 'Staff users and activity log' : ucfirst($permission)); @endphp
<section class="dash-card forbidden" aria-labelledby="forbidden-title">
    <span class="stat-icon stat-icon--violet"><i class="fas fa-lock" aria-hidden="true"></i></span>
    <h1 id="forbidden-title" class="dash-card__title">You don't have access to {{ $area }}</h1>
    <p class="dash-card__sub">Ask an administrator to turn this on for your account in Staff users.</p>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">Back to dashboard</a>
</section>
@endsection
