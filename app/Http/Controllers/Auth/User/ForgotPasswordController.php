<?php

namespace App\Http\Controllers\Auth\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\ForgotPassword;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Services\App\PasswordServices;
use App\Jobs\ProcessUserForgotPasswordMail;
use App\Http\Requests\UserForgotPasswordRequest;


class ForgotPasswordController extends Controller
{

    public function __construct()
    {
        $this->middleware(['guest']);
    }

    public function index()
    {        
        return view('forgotpassword');
    }

    public function request(UserForgotPasswordRequest $request, PasswordServices $services)
    {       
        $result = $services->request($request);  
        return back()->with($result);
    }

  
}
