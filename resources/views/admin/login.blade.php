@extends('layout.head')
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
                            <input type="email" name="email" value="{{ old('email') }}">
                           
                        </div>

                        <div class="form-block">
                            <label for="username">Password</label>
                            <input type="password" name="password">
                           
                        </div>

                        <div class="checkbox">
                            <input type="checkbox" name="remember" id="">
                            <label>Remember me<label>
                        </div>

                        <br>
                        
                        <input class="btn-w-12" type="submit"  value="Login">

                        <a href="{{ route('admin-forget-password') }}" class="link">Forgot Password ?</a>

                        <a href="/" class="link">Visit Store ?</a>


                    </form>
                </div>
            </div>
        </div>
        
    </div>


</body>

</html>