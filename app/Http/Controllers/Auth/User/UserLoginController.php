<?php

namespace App\Http\Controllers\Auth\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserLoginRequest;

class UserLoginController extends Controller
{

    public function __construct()
    {
        $this->middleware(['guest']);
    }
    
    public function index()
    {
        return view('login');
    }

    public function login(UserLoginRequest $request)
    { 

       $url = $request->input('url');
  
       if (auth()->attempt($request->only('email', 'password')))
       {
            if ($url == route('login.store')) return redirect()->route('home');
            return redirect()->to($url);
       
       }
       
       return back()->with('error','email not found');

    }
}
