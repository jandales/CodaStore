@extends('layout.front.app')
@section('content')    
    <div class="container">
        <div class="login-register-wrapper">
            <div class="login-register">
        
                    <div class="login-register-header">
                        <h1>Login</h1>                   
                    </div>
        
                    <div class="login-register-body"> 
                
                        @if (Session('error'))
                            <div class="alert alert-danger">{{ Session('error') }}</div>
                        @endif
            
                        <form class="form" action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="form-block">
                                <label for="username">Email</label>
                                <input name="email" type="username" placeholder="Email Address"  value="{{ old('email') }}">                                                                 
                            </div>
                            @error('email')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror  
                            <div class="form-block">
                                <label for="password">Password</label>     
                                <input name="password" type="password" placeholder="Password">  
                            </div>
                            @error('password')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <div class="flex space-between">
                                <div class="checkbox">
                                    <input name="rememberToken" type="checkbox">
                                    <label for="Remember me">Remember me </label>
                                </div>
                                <div class="forgor-password">
                                    <a href="/forgot-password" class="login-register-link">Forgot password?</a>
                                </div>
                            </div>                            
                            <br>
                            <button class="btn btn-dark">Login</button>
                        </form>  
                        <br>  
                        <a href="/register" class="login-register-link">Create account</a>
                    </div>
            
            </div>
        </div>   
    </div>       
@endsection
 