<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'Sign in | Coda Admin')</title>
</head>
<body class="admin-auth">
    <main id="main">
        <div class="admin-login-form">
            <a href="/" class="admin-wordmark" aria-label="Coda store">Coda <span>Admin</span></a>
            <div class="panel">
                <div class="panel-body">
                    <h1 class="panel-title">Sign in to the dashboard</h1>
                    <p class="admin-auth__sub">Use your staff account to manage products and orders.</p>

                    <form class="form" method="post" action="{{ route('admin.login.store') }}">
                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
                        @endif
                        @error('email')
                            <div class="alert alert-danger" role="alert">{{ $message }}</div>
                        @enderror
                        @error('password')
                            <div class="alert alert-danger" role="alert">{{ $message }}</div>
                        @enderror

                        @csrf

                        @php $demo = app()->environment('local'); @endphp
                        <div class="form-block">
                            <label for="admin-email">Email</label>
                            <input id="admin-email" type="email" name="email" autocomplete="username" value="{{ old('email', $demo ? 'admin@gmail.com' : '') }}">
                        </div>

                        <div class="form-block">
                            <div class="admin-auth__row">
                                <label for="admin-password">Password</label>
                                <a href="{{ route('admin.forget.password') }}" class="link">Forgot password?</a>
                            </div>
                            <input id="admin-password" type="password" name="password" autocomplete="current-password" @if($demo) value="password" @endif>
                        </div>

                        <input class="btn-w-12" type="submit" value="Sign in">

                        <a href="/" class="link admin-auth__back"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to store</a>
                    </form>
                </div>
            </div>
        </div>
    </main>
<script src="{{ asset('js/saving-forms.js') }}?v=3"></script>
</body>
</html>
