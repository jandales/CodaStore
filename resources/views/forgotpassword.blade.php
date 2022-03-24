@extends('layout.front.app1')

@section('content')
    

<div class="container">
    <div class="login-register-wrapper">
        <div class="login-register">
            <div class="login-register-header">
                <h1>Recover your password</h1>           
            </div>
            <div class="login-register-body"> 
                <div class="p"><p>Please Enter your email address to recover your account</p></div>
    
                        @if (Session('error'))
                            <div class="alert alert-danger m-10">{{ Session('error') }}</div>
                        @endif
    
                        @if (Session('success'))
                            <div class="alert alert-sucess .m-10">{{ Session('success') }}</div>
                        @endif
            
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
 