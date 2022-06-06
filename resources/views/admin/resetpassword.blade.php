@extends('layout.head')
<body>
    <div id="main">
        <div class="admin-login-form">
                <h1>Coda Store</h1>
            <div class="panel">
                <div class="panel-body">
                    <h4 class="panel-title">Reset Password</h4>
                    @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <p>Enter your Email address to Reset your password</p>

                    <form class="form" method="post" action="{{ route('admin.request.password') }}">
                        
                      
                       @error('email')
                             <div class="alert alert-error">{{ $message }}</div>
                       @enderror
                      
                        @csrf
                        <div class="form-block">
                            <label for="email">Email address</label>
                            <input type="email" name="email" value="{{ old('email') }}">
                          
                        </div>                    
                        
                        <input type="submit" class="btn-w-12"  value="Submit"> 
                    </form>

                    <a  href="{{ route('admin.login') }}" class="link">Login to dashbaord?</a>
                </div>
            </div>
        </div>
        
    </div>


</body>

</html>