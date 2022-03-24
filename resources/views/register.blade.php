@extends('layout.front.app')

@section('content')
    
    <div class="container">
        <div class="login-register-wrapper"> 
            <div class="login-register">
                <div class="login-register-header">
                    <h1>Sign up Now</h1>
                    <div class="p"><p>We make it easy for everyone to shop for clothing needs</p></div>
                </div>
                
                    <div class="login-register-body"> 
                        
                        <form action="{{ route('register') }}" method="post">
                            @csrf
                            <div class="form">
                                <div class="form-input">
                                    <div class="form-group-input">
                                        <span class="btn"><i class="fas fa-user"></i></span>
                                        <input type="text" name="name" placeholder="Name" value="{{ old('name')}}">
                                    </div>
                                    @error('name')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-input">
                                    <div class="form-group-input">
                                        <span class="btn"><i class="fas fa-user"></i></span>
                                        <input type="email" name="email" placeholder="Email" value="{{ old('email')}}">
                                    </div>
                                    @error('email')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-input">
                                    <div class="form-group-input">
                                        <span class="btn"><i class="fas fa-lock"></i></span>
                                        <input type="password" name="password" placeholder="Password">
                                    </div>
                                    @error('password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                                </div>
                                <button class="button" name="submit">Register</button>
                                    
                            </div>
                        </form>
                        <br>  
                        <span>You already have account? <a href="/login" class="login-register-link">Sign in</a></span>
                    
                
                
                        <div class="flex-row justify-content-center mt-2">
                            <div class="line"></div>
                            <span class="ml-1 mr-1">or</span>
                            <div class="line"></div>
                        </div>

                        <button class="login-register-social-button"><span><i class="fab fa-google"></i></span>Sign up with Google</button>
                    </div>    

            
            
            </div>
        </div>
    </div>           
    
 @endsection