<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/main.css')}}">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    <title>Document</title>
</head>
<body>
    <div id="main">
        <div class="admin-login-form">
                <h1>Coda Store</h1>
            <div class="panel">
                <div class="panel-body">
                    <h4 class="panel-title">Login to dashbaord</h4>
                    <p>Enter your credintials</p>

                    <form class="form" method="post" action="{{ route('admin.login.store') }}">
                        
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                      
                        @error('email')
                         <div class="alert alert-danger">>{{ $message }}</div>
                        <br>
                        @enderror

                        @error('password')
                         <div class="alert alert-danger">>{{ $message }}</div>
                        <br>
                        @enderror           
                      
                        @csrf
                        
                        <div class="form-block">
                            <label for="email">Username</label>
                            <input type="email" name="email" value="admin@gmail.com">
                           
                        </div>

                        <div class="form-block">
                            <label for="username">Password</label>
                            <input type="password" name="password" value="password">
                           
                        </div>

                        {{-- <div class="checkbox">
                            <input type="checkbox" name="remember" id="">
                            <label>Remember me<label>
                        </div> --}}
                        <div class="flex">
                            <a href="{{ route('admin.forget.password') }}" class="link ml-auto">Forgot Password ?</a>
                        </div>
                    

                        <br>
                        
                        <input class="btn-w-12" type="submit"  value="Login">

                      

                        <a href="/" class="link block m-t-1">Visit Store ?</a>


                    </form>
                </div>
            </div>
        </div>
        
    </div>


</body>

</html>