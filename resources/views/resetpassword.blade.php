@extends('layout.front.app')

@section('content')
    
<div class="container">
    <div class="login-register-wrapper">
        <div class="login-register">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if (session('success'))
             <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="login-register-header">
                <h1>Reset your password</h1>           
            </div>
            
            <div class="login-register-body">  
                        <form class="form" action="{{ route('password.store')}}" method="POST">
                           
                            @csrf  
                            <input type="hidden" name="token" value="{{$token}}" hidden>                  
        
                            <div class="form-block">
                                <label>New Password</label>                     
                                <input name="password" type="password">
        
                                @error('password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                              
                            </div>
        
                            <div class="form-block">
                                <label>Confirm Password</label>
                                <input name="password_confirmation" type="password">
        
                                @error('confirm_password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                              
                            </div>
                                <button class="button">Submit</button>
                                <br>
                        </form>
                        <br>
                        <a href="/login" class="login-register-link">Back to login</a>
            </div>
            
           
        
              
        </div>
    </div>
</div>












        
    
 @endsection
 