@extends('layout.front.app')

@section('content')    
    <div class="container">
        <div class="login-register-wrapper"> 
            <div class="login-register">
                <div class="login-register-header">
                    <h1>Create Account</h1>            
                </div>                
                <div class="login-register-body">                         
                    <form class="form" action="{{ route('register') }}" method="post">
                        @csrf 
                        <div class="form-block">
                            <label for="name">Name</label>
                            <input type="text" name="name"  value="{{ old('name')}}">
                        </div>  
                        @error('name')
                                <div class="alert alert-danger">{{ $message }}</div>
                        @enderror                               
                        <div class="form-block">
                            <label for="email">Email</label>
                            <input type="email" name="email"  value="{{ old('email')}}">
                        </div>
                        @error('email')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror                            
                        <div class="form-block">
                            <label for="password">Password</label>
                            <input type="password" name="password">
                        </div>
                        @error('password')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <button class="btn btn-dark" name="submit">Create</button>
                    </form>
                    <br>  
                    <label for="" class="block text-center">Or</label>
                    <br>
                    <a class="w-12 btn btn-border-dark mb-1" href="{{route('authGoogle')}}">
                        <i class="fa-brands fa-google"></i>
                        Register with Google</a>
                    <br>
                    <a href="/login" class="login-register-link">Login</a>
                </div>   
            </div>
        </div>
    </div>  
 @endsection