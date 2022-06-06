<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Admin\Auth\PasswordServices;
use App\Http\Requests\Admin\Auth\ResetPasswordRequest;
use App\Http\Requests\Admin\Auth\StoreResetPasswordRequest;

class AdminResetPasswordController extends Controller
{

    private $services;

    public function __construct(PasswordServices $services)
    {
        $this->services = $services;
    }

    public function index()
    {
        return view('admin.resetpassword');
    }

    public function request(ResetPasswordRequest $request)
    {  
        $result = $this->services->request($request);
        return back()->with($result);
    }

    public function reset($token)
    {
        return view('admin.adminresetpassword')->with('token', $token);
    }

    public function store(StoreResetPasswordRequest $request)
    { 
        $result = $this->services->store($request);
       
        return back()->with($result);

    }
}
