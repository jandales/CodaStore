@extends('layout.front.app')

@section('title', 'Confirm your email | Coda')

@section('content')
    <div class="container">
        <div class="login-register-wrapper">
            <div class="login-register verify-email">
                <div class="login-register-header">
                    <span class="verify-icon" aria-hidden="true"><i class="far fa-envelope"></i></span>
                    <h1>Confirm your email</h1>
                    <p class="login-register-sub">We sent a link to <strong>{{ auth()->user()->email }}</strong>. Open it to confirm your address, then you can check out.</p>
                </div>
                <div class="login-register-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="status">{{ session('success') }}</div>
                    @endif
                    <form method="post" action="{{ route('verification.send') }}">
                        @csrf
                        <button class="btn btn-dark btn-block">Send the link again</button>
                    </form>
                    <p class="verify-help">Can't find it? Check your spam folder. Wrong address? <a href="{{ route('account.edit') }}" class="link-underline">Update it in your account</a>.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
