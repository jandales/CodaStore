@extends('layout.front.app')

@section('content')
    

<div class="container">
    <div class="login-register-wrapper">
        @if (Session('error'))
        <div class="alert alert-danger m-10">{{ Session('error') }}</div>
    @endif
        <div class="login-register">
            <div class="login-register-header">
                <h1>Recover your Account</h1>      
                <br>     
            </div>
            <div class="login-register-body"> 
                @if (Session('success'))
                    <div class="alert alert-success .m-10">{{ Session('success') }}</div>
                @endif
                <div class="p"><p>Please Enter your email address to recover your account</p></div>
            
                        <form class="form" action="{{ route('password.request')}}" method="POST">                   
                            @csrf
                            <div class="form-block">
                                <label>Email</label>
                                <input name="email" type="text">
                                @error('email')
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
 