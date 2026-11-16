@extends('layout.front.app')
@section('title', 'Sign in | Coda')
@section('content')    
    <div class="container">
        <div class="login-register-wrapper">
            <div class="login-register">
        
                    @if (session('error'))
                        <div class="alert alert-danger">{{ Session('error') }}</div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    <div class="login-register-header">
                        <h1>Sign in</h1>
                        <p class="login-register-sub">Welcome back. Sign in to check out faster and track your orders.</p>                   
                    </div>
                   
                    <div class="login-register-body"> 
                
                     
            
                        <form class="form" action="{{ route('login')}}" method="POST">
                            @csrf
                            <input type="hidden" name="url" value="{{ url()->previous() }}">
                            <div class="form-block">
                                <label for="login-email">Email</label>
                                <input id="login-email" name="email" type="email" autocomplete="email" placeholder="you@example.com"  value="{{ old('email') }}">                                                                 
                            </div>
                            @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror  
                            <div class="form-block">
                                <label for="login-password">Password</label>
                                <input id="login-password" name="password" type="password" autocomplete="current-password">  
                            </div>
                            @error('password')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <div class="flex space-between">
                                {{-- <div class="checkbox">
                                    <input name="rememberToken" type="checkbox">
                                    <label for="Remember me">Remember me </label>
                                </div> --}}
                                <div class="forgor-password">
                                    <a href="/forgot-password" class="login-register-link">Forgot password?</a>
                                </div>
                            </div>                            
                            <br>
                            <button class="btn btn-dark w-12">Sign in</button>
                        </form>  
                        <br>  
                        <p class="login-register-alt">New to Coda? <a href="/register" class="link-underline">Create an account</a></p>
                        <br>
                        <div class="login-register-divider"><span>or</span></div>
                        <br>
                        <a class="w-12 btn btn-border-dark mb-1" href="{{route('authGoogle')}}">
                            <i class="fa-brands fa-google" aria-hidden="true"></i>
                            Continue with Google</a>
                        <br>
                        {{-- <a class="w-12 btn btn-dark" href="{{route('authGoogle')}}">Facebook</a> --}}
                    </div>
            
            </div>
        </div>   
    </div>       
@endsection
 