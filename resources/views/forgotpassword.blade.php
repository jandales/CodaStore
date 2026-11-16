@extends('layout.front.app')

@section('title', 'Reset your password | Coda')

@section('content')
    <div class="container">
        <div class="login-register-wrapper">
            <div class="login-register">
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success" role="status">{{ session('success') }}</div>
                @endif
                <div class="login-register-header">
                    <h1>Reset your password</h1>
                    <p class="login-register-sub">Enter the email you signed up with and we'll send you a link to reset your password.</p>
                </div>
                <div class="login-register-body">
                    <form class="form" action="{{ route('password.request') }}" method="POST">
                        @csrf
                        <div class="form-block">
                            <label for="forgot-email">Email</label>
                            <input id="forgot-email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" placeholder="you@example.com">
                            @error('email')
                                <div class="alert alert-danger" role="alert">{{ $message }}</div>
                            @enderror
                        </div>
                        <button class="btn btn-dark w-12">Send reset link</button>
                    </form>
                    <p class="login-register-alt"><a href="/login" class="link-underline">Back to sign in</a></p>
                </div>
            </div>
        </div>
    </div>
@endsection
