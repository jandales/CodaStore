@extends('layout.front.app')

@section('content')
    
<div class="container">
    <div class="login-register-wrapper">
        <div class="login-register">

            <div class="login-register-header">
                <h1>Reset your password</h1>           
            </div>
            
            <div class="login-register-body">       
                        @if (Session('error'))
                            <div class="alert alert-danger">{{ Session('error') }}</div>
                        @endif
              
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
            </div>
            
           
        
              
        </div>
    </div>
</div>












        
    
 @endsection
 