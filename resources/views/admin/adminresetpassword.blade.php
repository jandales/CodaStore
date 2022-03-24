@extends('layout.head')
<body>
    <div id="main">
        <div class="admin-login-form">
                <h1>Coda Store</h1>
            <div class="panel">
                <div class="panel-body">
                    <h4 class="panel-title">Reset Password</h4>
                    <p>Enter your new password</p>

                    <form class="form"  method="post" action="{{ route('admin-reset-password') }}">
                        
                        @csrf

                        @if (session('success'))
                            <p>{{ session('success') }}</p>
                            <br>
                        @endif 

                    
                        @if (session('error'))
                            <p>{{ session('error') }}</p>
                            <br>
                        @endif 

                        @error('password')
                            <label>{{ $message }}</label>
                            <br>
                        @enderror

                        @error('password_confirmation')
                            <label>{{ $message }}</label>
                            <br>
                        @enderror
                        
                        <div class="form-block">
                            <label for="password">Password</label>
                            <input type="password" name="password" value="">
                         
                        </div> 
                        
                        <div class="form-block">
                            <label for="password">Confirm Password</label>
                            <input type="password" name="password_confirmation" value="">
                          
                        </div>
                        
                        <input type="submit" class="btn-w-12"  value="Submit"> 
                    </form>

                    <a  href="{{ route('admin-login') }}" class="link">Login to dashbaord?</a>
                </div>
            </div>
        </div>
        
    </div>


</body>

</html>