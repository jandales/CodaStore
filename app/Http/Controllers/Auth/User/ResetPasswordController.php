<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Services\App\PasswordServices;
use App\Http\Requests\UserResetPasswordRequest;

class ResetPasswordController extends Controller
{
  
    public function __construct()
    {
        $this->middleware(['guest']);       
    }

    public function index($token)
    {       
        return view('resetpassword')->with('token',$token);
    }

    public function reset(UserResetPasswordRequest $request, PasswordServices $services)
    {         
        $result = $services->reset($request);
        
        if(!$result[0]) 
            return redirect()->route('password.forgot')->with($result[1]);

        return redirect()->route('users.login');
    }
}
