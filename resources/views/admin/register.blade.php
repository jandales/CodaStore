@extends('layout.head')
<body>
    <div id="main">
        <div class="admin-login-form">
                <h1>Coda Store</h1>
            <div class="panel">
                <div class="panel-body">
                    <h4 class="panel-title">Create admin Account</h4>
                    <p>Fill all Information</p>
                    <form class="form" method="post" action="{{ route('admin-register')}}">
                        @csrf
                        <div class="form-block">
                            <label for="username">Name</label>
                            <input type="username" name="name">
                        </div>

                        <div class="form-block">
                            <label for="username">Email</label>
                            <input type="username" name="email">
                        </div>

                        <div class="form-block">
                            <label for="username">Password</label>
                            <input type="password" name="password">
                        </div>

                        <input class="btn-w-12" type="submit" value="Create Account">
                        <br>
                        <a href="{{ route('admin-reset-password') }}" class="link m-10">Forgot Password ?</a>

                    </form>
                </div>
            </div>
        </div>
        
    </div>


</body>

</html>