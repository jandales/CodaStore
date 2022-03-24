@extends('layout.front.app')
@section('content')    
    <div class="container">
        <div class="login-register-wrapper">
            <div class="login-register">
        
                    <div class="login-register-header">
                        <h1>Welcome back</h1>
                        <div class="p"><p>We make it easy for everyone to shop for clothing needs</p></div>
                    </div>
        
                    <div class="login-register-body"> 
                
                        @if (Session('error'))
                            <div class="alert alert-danger">{{ Session('error') }}</div>
                        @endif
            
                        <form class="form form-login" action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="form-input">
                                <div class="form-group-input">
                                    <span class="btn"><i class="fas fa-user"></i></span>
                                    <input name="email" type="username" placeholder="Email Address"  value="{{ old('email') }}">
                                </div>
                                
                                @error('email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror                      
                            </div>
                            <div class="form-input">
                                <div class="form-group-input">
                                    <span class="btn"><i class="fas fa-lock"></i></span>
                                    <input name="password" type="password" placeholder="Password">
                                </div>
                                
                                @error('password')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="flex-row space-between">
                                <div class="checkbox">
                                    <input name="rememberToken" type="checkbox">
                                    <label for="Remember me">Remember me </label>
                                </div>
                                <div class="forgor-password">
                                    <a href="/forgot-password" class="login-register-link">Forgot password?</a>
                                </div>
                            </div>
                            
                            <br>
                            <button>Sign in</button>
                        </form>  
                        <br>  
                        <span>Dont have an account yet? <a href="/register" class="login-register-link"> Sign up now</a></span>
                        

                        <div class="flex-row justify-content-center mt-2">
                            <div class="line"></div>
                            <span class="ml-1 mr-1">or</span>
                            <div class="line"></div>
                        </div>

                        <button class="login-register-social-button"><span><i class="fab fa-google"></i></span>Continue with Google</button>
                    </div>
            
            </div>
        </div>   
    </div>       
@endsection
 