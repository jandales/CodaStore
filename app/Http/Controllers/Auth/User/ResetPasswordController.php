<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use App\Services\App\PasswordServices;
use App\Http\Requests\UserResetPasswordRequest;

class ResetPasswordController extends Controller
{
    private PasswordServices $services;

    public function __construct(PasswordServices $services)
    {
        $this->middleware(['guest']);       
        $this->services = $services;
    }

    public function index($token)
    {
       
        $isValid = $this->services->validateToken($token);
        
        if ( ! $isValid ) return view('resetpassword')->with(['token' => $token, 'error' => 'Request already expires']);
  
        return view('resetpassword')->with(['token' => $token, 'error' => null]);
    }

    public function reset(UserResetPasswordRequest $request)
    {         
        $result = $this->services->reset($request); 
        if ($result['status'] === false) return back()->with('error',$result['message']);  
        return redirect()->route('login')->with('success', $result['message']);      
    }
}
