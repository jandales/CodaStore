@extends('layout.front.app')
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
                        <h1>Login</h1>                   
                    </div>
                   
                    <div class="login-register-body"> 
                
                     
            
                        <form class="form" action="{{ route('login')}}" method="POST">
                            @csrf
                            <input type="hidden" name="url" value="{{ url()->previous() }}">
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
                                {{-- <div class="checkbox">
                                    <input name="rememberToken" type="checkbox">
                                    <label for="Remember me">Remember me </label>
                                </div> --}}
                                <div class="forgor-password">
                                    <a href="/forgot-password" class="login-register-link">Forgot password?</a>
                                </div>
                            </div>                            
                            <br>
                            <button class="btn btn-dark">Login</button>
                        </form>  
                        <br>  
                        <a href="/register" class="login-register-link">Create account</a>
                        <br>
                        <label for="" class="block text-center">Or</label>
                        <br>
                        <a class="w-12 btn btn-border-dark mb-1" href="{{route('authGoogle')}}">
                            <i class="fa-brands fa-google"></i>
                            Login with Google</a>
                        <br>
                        {{-- <a class="w-12 btn btn-dark" href="{{route('authGoogle')}}">Facebook</a> --}}
                    </div>
            
            </div>
        </div>   
    </div>       
@endsection
 