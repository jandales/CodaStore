@extends('layout.front.app')

@section('title', 'Create an account | Coda')
@section('content')    
    <div class="container">
        <div class="login-register-wrapper"> 
            <div class="login-register">
                <div class="login-register-header">
                    <h1>Create an account</h1>
                    <p class="login-register-sub">Save your details for faster checkout and easy order tracking.</p>            
                </div>                
                <div class="login-register-body">                         
                    <form class="form" action="{{ route('register') }}" method="post">
                        @csrf 
                        <div class="form-block">
                            <label for="reg-name">Name</label>
                            <input id="reg-name" type="text" autocomplete="name" name="name"  value="{{ old('name')}}">
                        </div>  
                        @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                        @enderror                               
                        <div class="form-block">
                            <label for="reg-email">Email</label>
                            <input id="reg-email" type="email" autocomplete="email" name="email"  value="{{ old('email')}}">
                        </div>
                        @error('email')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror                            
                        <div class="form-block">
                            <label for="reg-password">Password</label>
                            <input id="reg-password" type="password" autocomplete="new-password" name="password">
                        </div>
                        @error('password')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <button class="btn btn-dark w-12" name="submit">Create account</button>
                    </form>
                    <br>  
                    <div class="login-register-divider"><span>or</span></div>
                    <br>
                    <a class="w-12 btn btn-border-dark mb-1" href="{{route('authGoogle')}}">
                        <i class="fa-brands fa-google" aria-hidden="true"></i>
                        Continue with Google</a>
                    <br>
                    <p class="login-register-alt">Already have an account? <a href="/login" class="link-underline">Sign in</a></p>
                </div>   
            </div>
        </div>
    </div>  
 @endsection